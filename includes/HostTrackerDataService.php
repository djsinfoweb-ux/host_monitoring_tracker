<?php
namespace Modules\HostMonitoringTracker\Includes;

use API;

/**
 * Centraliza a leitura dos grupos, limites e contagem de hosts.
 *
 * A hierarquia é inferida pelo nome do grupo. Exemplo:
 * - DJSINFOWEB
 * - DJSINFOWEB/APPS
 * - DJSINFOWEB/VMs
 *
 * Um grupo só vira filho quando o prefixo do caminho também existe como
 * grupo visível no Zabbix. Dessa forma, nomes que apenas possuem "/" não
 * são agrupados incorretamente.
 */
final class HostTrackerDataService {
    private const DEFAULT_LIMIT = 100;

    /** @var string */
    private $limitsFile;

    public function __construct($limitsFile = null) {
        $this->limitsFile = $limitsFile ?: dirname(__DIR__) . '/limits.json';
    }

    /**
     * Retorna os grupos em ordem hierárquica, prontos para a tela e PDF.
     *
     * @return array{rows: array, stats: array}
     */
    public function getHierarchyData() {
        try {
            $groups = API::HostGroup()->get([
                'output' => ['groupid', 'name'],
                'sortfield' => 'name'
            ]);

            if (!$groups) {
                return $this->emptyData();
            }

            $limits = $this->loadLimits();
            $counts = $this->getMonitoredHostCounts($groups);
            $rows = [];

            foreach ($groups as $group) {
                $groupId = (string) $group['groupid'];
                $current = isset($counts[$groupId]) ? (int) $counts[$groupId] : 0;
                $contracted = isset($limits[$groupId])
                    ? max(0, (int) $limits[$groupId])
                    : self::DEFAULT_LIMIT;

                $rows[] = [
                    'groupid' => $groupId,
                    'name' => (string) $group['name'],
                    'contracted' => $contracted,
                    'current_limit' => $contracted,
                    'current' => $current,
                    'hosts_count' => $current,
                    'status' => ($current > $contracted) ? 'Acima do Limite' : 'OK'
                ];
            }

            return self::buildHierarchy($rows);
        }
        catch (\Throwable $e) {
            error_log('Host Monitoring Tracker: erro ao carregar dados: ' . $e->getMessage());
            return $this->emptyData();
        }
    }

    /**
     * Salva limites de forma atômica, evitando arquivo JSON parcialmente gravado.
     */
    public function saveLimits(array $submittedLimits) {
        $limits = $this->loadLimits();

        foreach ($submittedLimits as $groupId => $value) {
            $groupId = (string) $groupId;

            if (!preg_match('/^\d+$/', $groupId)) {
                continue;
            }

            if (is_array($value) || !is_numeric($value)) {
                continue;
            }

            $limits[$groupId] = max(0, min(99999, (int) $value));
        }

        ksort($limits, SORT_NATURAL);

        $json = json_encode($limits, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        $directory = dirname($this->limitsFile);
        if (!is_dir($directory) || !is_writable($directory)) {
            // Se o diretório não for gravável, ainda pode ser possível sobrescrever
            // o arquivo existente. Nesse caso, tentamos a escrita direta com LOCK_EX.
            $written = @file_put_contents($this->limitsFile, $json . PHP_EOL, LOCK_EX);
            return $written !== false;
        }

        $temporaryFile = $this->limitsFile . '.tmp.' . getmypid();
        $written = @file_put_contents($temporaryFile, $json . PHP_EOL, LOCK_EX);

        if ($written === false) {
            return false;
        }

        @chmod($temporaryFile, 0666);

        if (!@rename($temporaryFile, $this->limitsFile)) {
            @unlink($temporaryFile);
            return false;
        }

        @chmod($this->limitsFile, 0666);
        return true;
    }

    /**
     * Monta árvore com suporte a múltiplos níveis.
     * Método público para facilitar testes sem depender da API do Zabbix.
     *
     * @param array $rows Linhas contendo ao menos groupid e name.
     * @return array{rows: array, stats: array}
     */
    public static function buildHierarchy(array $rows) {
        if (!$rows) {
            return [
                'rows' => [],
                'stats' => [
                    'root_groups' => 0,
                    'child_groups' => 0,
                    'total_groups' => 0
                ]
            ];
        }

        $rowsById = [];
        $idByNormalizedName = [];

        foreach ($rows as $row) {
            $groupId = (string) $row['groupid'];
            $row['groupid'] = $groupId;
            $row['normalized_name'] = self::normalizeGroupPath((string) $row['name']);
            $row['parent_groupid'] = null;
            $row['depth'] = 0;
            $row['has_children'] = false;

            $rowsById[$groupId] = $row;

            // Mantém o primeiro grupo caso dois nomes diferentes resultem no
            // mesmo caminho normalizado.
            if (!isset($idByNormalizedName[$row['normalized_name']])) {
                $idByNormalizedName[$row['normalized_name']] = $groupId;
            }
        }

        $childrenByParent = [];
        $rootIds = [];

        foreach ($rowsById as $groupId => &$row) {
            $parentId = self::findNearestExistingParent(
                $groupId,
                $row['normalized_name'],
                $idByNormalizedName
            );

            if ($parentId !== null) {
                $row['parent_groupid'] = $parentId;
                $childrenByParent[$parentId][] = $groupId;
            }
            else {
                $rootIds[] = $groupId;
            }
        }
        unset($row);

        foreach ($childrenByParent as $parentId => $childIds) {
            if (isset($rowsById[$parentId])) {
                $rowsById[$parentId]['has_children'] = true;
            }
        }

        $sortIds = static function (&$ids) use (&$rowsById) {
            usort($ids, static function ($leftId, $rightId) use (&$rowsById) {
                $comparison = strnatcasecmp($rowsById[$leftId]['name'], $rowsById[$rightId]['name']);

                if ($comparison !== 0) {
                    return $comparison;
                }

                return strnatcasecmp((string) $leftId, (string) $rightId);
            });
        };

        $sortIds($rootIds);
        foreach ($childrenByParent as &$childIds) {
            $sortIds($childIds);
        }
        unset($childIds);

        $flattenedRows = [];
        $appendBranch = static function ($groupId, $depth) use (
            &$appendBranch,
            &$flattenedRows,
            &$rowsById,
            &$childrenByParent
        ) {
            $rowsById[$groupId]['depth'] = $depth;
            $flattenedRows[] = $rowsById[$groupId];

            if (!empty($childrenByParent[$groupId])) {
                foreach ($childrenByParent[$groupId] as $childId) {
                    $appendBranch($childId, $depth + 1);
                }
            }
        };

        foreach ($rootIds as $rootId) {
            $appendBranch($rootId, 0);
        }

        $total = count($flattenedRows);
        $roots = count($rootIds);

        return [
            'rows' => $flattenedRows,
            'stats' => [
                'root_groups' => $roots,
                'child_groups' => $total - $roots,
                'total_groups' => $total
            ]
        ];
    }

    private function loadLimits() {
        if (!is_file($this->limitsFile)) {
            return [];
        }

        $content = @file_get_contents($this->limitsFile);
        if ($content === false || trim($content) === '' || trim($content) === '{}') {
            return [];
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Faz uma única consulta de hosts e conta a associação com cada grupo.
     * Caso uma versão/instalação não aceite selectHostGroups, usa fallback.
     */
    private function getMonitoredHostCounts(array $groups) {
        $counts = [];

        foreach ($groups as $group) {
            $counts[(string) $group['groupid']] = 0;
        }

        try {
            $hosts = API::Host()->get([
                'output' => ['hostid'],
                'filter' => ['status' => 0],
                'selectHostGroups' => ['groupid']
            ]);

            foreach ($hosts as $host) {
                $hostGroups = isset($host['hostgroups']) && is_array($host['hostgroups'])
                    ? $host['hostgroups']
                    : [];

                foreach ($hostGroups as $hostGroup) {
                    $groupId = isset($hostGroup['groupid']) ? (string) $hostGroup['groupid'] : '';
                    if ($groupId !== '' && array_key_exists($groupId, $counts)) {
                        $counts[$groupId]++;
                    }
                }
            }

            return $counts;
        }
        catch (\Throwable $e) {
            error_log(
                'Host Monitoring Tracker: fallback de contagem por grupo: ' . $e->getMessage()
            );
        }

        foreach ($groups as $group) {
            $groupId = (string) $group['groupid'];

            try {
                $counts[$groupId] = (int) API::Host()->get([
                    'groupids' => $groupId,
                    'filter' => ['status' => 0],
                    'countOutput' => true
                ]);
            }
            catch (\Throwable $e) {
                $counts[$groupId] = 0;
            }
        }

        return $counts;
    }

    private static function findNearestExistingParent($groupId, $normalizedName, array $idByNormalizedName) {
        $parts = explode('/', $normalizedName);

        if (count($parts) < 2) {
            return null;
        }

        array_pop($parts);

        while ($parts) {
            $candidateName = implode('/', $parts);

            if (isset($idByNormalizedName[$candidateName])) {
                $candidateId = (string) $idByNormalizedName[$candidateName];
                if ($candidateId !== (string) $groupId) {
                    return $candidateId;
                }
            }

            array_pop($parts);
        }

        return null;
    }

    private static function normalizeGroupPath($name) {
        $parts = array_map('trim', explode('/', trim($name)));
        $parts = array_values(array_filter($parts, static function ($part) {
            return $part !== '';
        }));

        return implode('/', $parts);
    }

    private function emptyData() {
        return [
            'rows' => [],
            'stats' => [
                'root_groups' => 0,
                'child_groups' => 0,
                'total_groups' => 0
            ]
        ];
    }
}
