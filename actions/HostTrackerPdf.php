<?php
namespace Modules\HostMonitoringTracker\Actions;

use CController;
use Modules\HostMonitoringTracker\Includes\HostTrackerDataService;

require_once dirname(__DIR__) . '/includes/HostTrackerDataService.php';

class HostTrackerPdf extends CController {

    protected function init() {
        $this->disableCsrfValidation();
    }

    protected function checkInput() {
        return true;
    }

    protected function checkPermissions() {
        return true;
    }

    protected function doAction() {
        $dataset = (new HostTrackerDataService())->getHierarchyData();
        $html = $this->generateHtml($dataset['rows'], $dataset['stats']);

        header('Content-Type: text/html; charset=utf-8');
        header(
            'Content-Disposition: inline; filename="acompanhamento_hosts_'
            . date('Y-m-d')
            . '.html"'
        );

        echo $html;
        exit;
    }

    private function generateHtml(array $companies, array $stats) {
        $generatedAt = date('d/m/Y H:i:s');
        $rootGroups = isset($stats['root_groups']) ? (int) $stats['root_groups'] : 0;
        $childGroups = isset($stats['child_groups']) ? (int) $stats['child_groups'] : 0;
        $totalGroups = isset($stats['total_groups']) ? (int) $stats['total_groups'] : count($companies);

        $html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acompanhamento de Host</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #fff;
            color: #222;
        }
        h1 {
            text-align: center;
            color: #333;
            border-bottom: 3px solid #0275b8;
            padding-bottom: 10px;
        }
        .info {
            margin: 20px 0;
            padding: 12px;
            background: #f5f5f5;
            border-left: 4px solid #0275b8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #0275b8;
            color: #fff;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        th:nth-child(n+2),
        td:nth-child(n+2) {
            text-align: center;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr.parent-row td {
            background-color: #eef6fc;
            font-weight: bold;
        }
        .group-name {
            display: inline-block;
        }
        .tree-marker {
            display: inline-block;
            width: 18px;
            color: #0275b8;
            font-weight: bold;
        }
        .status-ok {
            color: #2e7d32;
            font-weight: bold;
        }
        .status-overlimit,
        .current-overlimit {
            color: #c62828;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .print-button {
            padding: 10px 20px;
            background: #0275b8;
            color: #fff;
            border: 0;
            cursor: pointer;
            margin-bottom: 20px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <h1>ACOMPANHAMENTO DE HOST</h1>

    <div class="info">
        <strong>Data de geração:</strong> ' . htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') . '<br>
        <strong>Empresas/grupos principais:</strong> ' . $rootGroups . '<br>
        <strong>Subgrupos:</strong> ' . $childGroups . '<br>
        <strong>Total de grupos:</strong> ' . $totalGroups . '
    </div>

    <button class="no-print print-button" onclick="window.print()">
        Imprimir / Salvar como PDF
    </button>

    <table>
        <thead>
            <tr>
                <th>Empresa</th>
                <th>Contratado</th>
                <th>Atual</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($companies as $company) {
            $isOk = $company['status'] === 'OK';
            $statusClass = $isOk ? 'status-ok' : 'status-overlimit';
            $currentClass = $isOk ? '' : ' class="current-overlimit"';
            $rowClass = !empty($company['has_children']) ? ' class="parent-row"' : '';
            $depth = isset($company['depth']) ? max(0, (int) $company['depth']) : 0;
            $padding = 10 + ($depth * 24);
            $marker = !empty($company['has_children']) ? '&#9662;' : ($depth > 0 ? '&#8627;' : '');

            $html .= '<tr' . $rowClass . '>
                <td style="padding-left: ' . $padding . 'px;">'
                    . '<span class="tree-marker">' . $marker . '</span>'
                    . '<span class="group-name">'
                    . htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8')
                    . '</span>'
                . '</td>
                <td>' . (int) $company['contracted'] . '</td>
                <td' . $currentClass . '>' . (int) $company['current'] . '</td>
                <td class="' . $statusClass . '">'
                    . htmlspecialchars($company['status'], ENT_QUOTES, 'UTF-8')
                . '</td>
            </tr>';
        }

        if (!$companies) {
            $html .= '<tr><td colspan="4">Nenhum grupo encontrado.</td></tr>';
        }

        $html .= '
        </tbody>
    </table>

    <div class="footer">
        <p>Documento gerado automaticamente pelo módulo Host Monitoring Tracker do Zabbix.</p>
    </div>
</body>
</html>';

        return $html;
    }
}
