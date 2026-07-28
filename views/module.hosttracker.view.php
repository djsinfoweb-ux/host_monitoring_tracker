<?php
/**
 * @var CView $this
 * @var array $data
 */

$buttons = [];

if ($data['canManageLimits']) {
    $buttons[] = (new CButton('manage_limits', _('Gerenciar Limites')))
        ->addClass(ZBX_STYLE_BTN_ALT)
        ->setAttribute('onclick', 'window.location.href="zabbix.php?action=hosttracker.edit"');
}

$buttons[] = (new CButton('export_pdf', _('Gerar PDF')))
    ->addClass(ZBX_STYLE_BTN_ALT)
    ->setAttribute('onclick', 'window.location.href="zabbix.php?action=hosttracker.pdf"');

$widget = (new CHtmlPage())
    ->setTitle(_('Acompanhamento de Host'))
    ->setControls(new CList($buttons));

$helpText = (new CDiv())
    ->addClass('hosttracker-hierarchy-help')
    ->addItem([
        (new CSpan('▾'))->addClass('hosttracker-help-icon'),
        _('Clique na seta ao lado de uma empresa para abrir ou fechar seus subgrupos.')
    ]);

$widget->addItem($helpText);

$table = (new CTableInfo())
    ->setId('hosttracker-table')
    ->addClass('hosttracker-hierarchy-table')
    ->setNoDataMessage(_('Nenhuma empresa ou grupo encontrado'))
    ->setHeader([
        _('Empresa'),
        _('Contratado'),
        _('Atual'),
        _('Status')
    ]);

foreach ($data['companies'] as $company) {
    $groupId = (string) $company['groupid'];
    $depth = isset($company['depth']) ? max(0, (int) $company['depth']) : 0;
    $hasChildren = !empty($company['has_children']);
    $parentId = isset($company['parent_groupid']) && $company['parent_groupid'] !== null
        ? (string) $company['parent_groupid']
        : '';

    $nameContent = (new CDiv())->addClass('hosttracker-name-content');

    if ($hasChildren) {
        $toggle = (new CTag('button', true,
            (new CSpan())->addClass('hosttracker-caret')
        ))
            ->addClass('hosttracker-toggle')
            ->setAttribute('type', 'button')
            ->setAttribute('aria-expanded', 'true')
            ->setAttribute(
                'aria-label',
                sprintf(_('Recolher ou expandir os subgrupos de %1$s'), $company['name'])
            )
            ->setAttribute('title', _('Abrir/fechar subgrupos'));

        $nameContent->addItem($toggle);
    }
    else {
        $nameContent->addItem(
            (new CSpan())->addClass('hosttracker-toggle-spacer')
        );
    }

    $nameContent->addItem(
        (new CSpan($company['name']))->addClass('hosttracker-group-name')
    );

    $nameCell = (new CCol($nameContent))
        ->addClass('hosttracker-company-cell')
        ->addStyle('padding-left: ' . (8 + ($depth * 24)) . 'px;');

    $statusClass = $company['status'] === 'OK'
        ? 'status-ok'
        : 'status-overlimit';

    $statusCell = (new CCol(
        (new CSpan($company['status']))->addClass($statusClass)
    ))->addClass('hosttracker-status-cell');

    $currentSpan = (new CSpan((string) $company['current']))
        ->addClass('hosttracker-current-value');

    if ($company['status'] !== 'OK') {
        $currentSpan->addClass('atual-overlimit');
    }

    $row = (new CRow([
        $nameCell,
        (new CCol((string) $company['contracted']))->addClass('hosttracker-number-cell'),
        (new CCol($currentSpan))->addClass('hosttracker-number-cell'),
        $statusCell
    ]))
        ->setId('hosttracker-row-' . $groupId)
        ->addClass('hosttracker-row')
        ->setAttribute('data-group-id', $groupId)
        ->setAttribute('data-depth', (string) $depth)
        ->setAttribute('data-has-children', $hasChildren ? '1' : '0');

    if ($parentId !== '') {
        $row->setAttribute('data-parent-id', $parentId);
        $row->addClass('hosttracker-child-row');
    }

    if ($hasChildren) {
        $row->addClass('hosttracker-parent-row');
    }

    $table->addRow($row);
}

$widget->addItem($table);

$stats = isset($data['stats']) ? $data['stats'] : [];
$rootGroups = isset($stats['root_groups']) ? (int) $stats['root_groups'] : 0;
$childGroups = isset($stats['child_groups']) ? (int) $stats['child_groups'] : 0;
$totalGroups = isset($stats['total_groups']) ? (int) $stats['total_groups'] : count($data['companies']);

$infoDiv = (new CDiv())
    ->addClass('info-container hosttracker-summary')
    ->addItem([
        (new CTag('strong', true, _('Empresas/grupos principais: '))),
        (string) $rootGroups,
        ' | ',
        (new CTag('strong', true, _('Subgrupos: '))),
        (string) $childGroups,
        ' | ',
        (new CTag('strong', true, _('Total de grupos: '))),
        (string) $totalGroups
    ]);

$widget->addItem($infoDiv);
$widget->show();
