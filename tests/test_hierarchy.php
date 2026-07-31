<?php
require_once dirname(__DIR__) . '/includes/HostTrackerDataService.php';

use Modules\HostMonitoringTracker\Includes\HostTrackerDataService;

function assertSameValue($expected, $actual, $message) {
    if ($expected !== $actual) {
        fwrite(
            STDERR,
            "FALHA: {$message}. Esperado: " . var_export($expected, true)
            . "; recebido: " . var_export($actual, true) . PHP_EOL
        );
        exit(1);
    }
}

$dataset = HostTrackerDataService::buildHierarchy([
    [
        'groupid' => '1',
        'name' => 'DJSINFOWEB',
        'current' => 2,
        'hosts_count' => 2,
        'contracted' => 8,
        'current_limit' => 8,
        'status' => 'OK'
    ],
    [
        'groupid' => '2',
        'name' => 'DJSINFOWEB/APPS',
        'current' => 4,
        'hosts_count' => 4,
        'contracted' => 100,
        'current_limit' => 100,
        'status' => 'OK'
    ],
    [
        'groupid' => '3',
        'name' => 'DJSINFOWEB/VMs',
        'current' => 5,
        'hosts_count' => 5,
        'contracted' => 100,
        'current_limit' => 100,
        'status' => 'OK'
    ]
]);

$rowsById = [];
foreach ($dataset['rows'] as $row) {
    $rowsById[$row['groupid']] = $row;
}

assertSameValue(9, $rowsById['1']['current'], 'o pai deve somar 4 + 5');
assertSameValue(9, $rowsById['1']['hosts_count'], 'hosts_count do pai deve ser consolidado');
assertSameValue(2, $rowsById['1']['direct_current'], 'a contagem direta original deve ser preservada');
assertSameValue(true, $rowsById['1']['is_aggregated'], 'o pai deve ser identificado como agregado');
assertSameValue('Acima do Limite', $rowsById['1']['status'], 'status do pai deve usar 9 contra limite 8');
assertSameValue(4, $rowsById['2']['current'], 'folha APPS deve manter contagem direta');
assertSameValue(5, $rowsById['3']['current'], 'folha VMs deve manter contagem direta');

$multiLevel = HostTrackerDataService::buildHierarchy([
    ['groupid' => '10', 'name' => 'EMPRESA', 'current' => 0, 'contracted' => 20],
    ['groupid' => '11', 'name' => 'EMPRESA/APPS', 'current' => 7, 'contracted' => 20],
    ['groupid' => '12', 'name' => 'EMPRESA/APPS/PRD', 'current' => 3, 'contracted' => 20],
    ['groupid' => '13', 'name' => 'EMPRESA/APPS/HML', 'current' => 2, 'contracted' => 20],
    ['groupid' => '14', 'name' => 'EMPRESA/VMs', 'current' => 5, 'contracted' => 20]
]);

$multiById = [];
foreach ($multiLevel['rows'] as $row) {
    $multiById[$row['groupid']] = $row;
}

assertSameValue(5, $multiById['11']['current'], 'pai intermediário deve somar PRD 3 + HML 2');
assertSameValue(10, $multiById['10']['current'], 'raiz deve somar APPS 5 + VMs 5');
assertSameValue(7, $multiById['11']['direct_current'], 'contagem direta do pai intermediário deve ser preservada');

$withSpaces = HostTrackerDataService::buildHierarchy([
    ['groupid' => '20', 'name' => 'CLIENTE', 'current' => 1, 'contracted' => 10],
    ['groupid' => '21', 'name' => 'CLIENTE / APP', 'current' => 4, 'contracted' => 10],
    ['groupid' => '22', 'name' => 'CLIENTE / VM', 'current' => 5, 'contracted' => 10],
    ['groupid' => '23', 'name' => 'SEM_PAI / APP', 'current' => 6, 'contracted' => 10]
]);

$spacesById = [];
foreach ($withSpaces['rows'] as $row) {
    $spacesById[$row['groupid']] = $row;
}

assertSameValue(9, $spacesById['20']['current'], 'espaços ao redor da barra devem manter a consolidação');
assertSameValue('20', $spacesById['21']['parent_groupid'], 'CLIENTE / APP deve ser filho de CLIENTE');
assertSameValue(null, $spacesById['23']['parent_groupid'], 'grupo sem pai existente deve permanecer como raiz');
assertSameValue(6, $spacesById['23']['current'], 'grupo sem pai existente deve manter contagem direta');

fwrite(STDOUT, "OK: testes de hierarquia e consolidação concluídos.\n");
