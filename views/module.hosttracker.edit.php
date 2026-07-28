<?php
/**
 * @var CView $this
 * @var array $data
 */

$widget = (new CHtmlPage())
    ->setTitle(_('Gerenciar Limites de Hosts'));

$widget->addItem(
    (new CDiv())
        ->addClass('hosttracker-hierarchy-help')
        ->addItem([
            (new CSpan('▾'))->addClass('hosttracker-help-icon'),
            _('Clique na seta para abrir ou fechar os subgrupos da empresa.')
        ])
);

$form = (new CForm())
    ->setId('limits_form')
    ->setName('limits_form')
    ->setAction('zabbix.php?action=hosttracker.edit')
    ->setMethod('post');

$form->addItem(new CInput('hidden', 'save', '1'));

$table = (new CTableInfo())
    ->setId('hosttracker-limits-table')
    ->addClass('hosttracker-hierarchy-table hosttracker-limits-table')
    ->setNoDataMessage(_('Nenhum grupo encontrado'))
    ->setHeader([
        (new CColHeader(_('Grupo/Empresa')))->addStyle('text-align: left;'),
        (new CColHeader(_('Hosts Monitorados')))->addStyle('text-align: center;'),
        (new CColHeader(_('Limite Atual')))->addStyle('text-align: center;'),
        (new CColHeader(_('Novo Limite')))->addStyle('text-align: center;')
    ]);

foreach ($data['groups'] as $group) {
    $groupId = (string) $group['groupid'];
    $name = (string) $group['name'];
    $limit = (int) $group['current_limit'];
    $count = (int) $group['hosts_count'];
    $depth = isset($group['depth']) ? max(0, (int) $group['depth']) : 0;
    $hasChildren = !empty($group['has_children']);
    $parentId = isset($group['parent_groupid']) && $group['parent_groupid'] !== null
        ? (string) $group['parent_groupid']
        : '';

    $input = (new CTextBox('limits[' . $groupId . ']', (string) $limit))
        ->setWidth(ZBX_TEXTAREA_SMALL_WIDTH)
        ->setAttribute('type', 'number')
        ->setAttribute('min', '0')
        ->setAttribute('max', '99999')
        ->setAttribute('step', '1');

    $nameContent = (new CDiv())->addClass('hosttracker-name-content');

    if ($hasChildren) {
        $nameContent->addItem(
            (new CTag('button', true,
                (new CSpan())->addClass('hosttracker-caret')
            ))
                ->addClass('hosttracker-toggle')
                ->setAttribute('type', 'button')
                ->setAttribute('aria-expanded', 'true')
                ->setAttribute(
                    'aria-label',
                    sprintf(_('Recolher ou expandir os subgrupos de %1$s'), $name)
                )
                ->setAttribute('title', _('Abrir/fechar subgrupos'))
        );
    }
    else {
        $nameContent->addItem(
            (new CSpan())->addClass('hosttracker-toggle-spacer')
        );
    }

    $nameContent->addItem(
        (new CSpan($name))->addClass('hosttracker-group-name')
    );

    $hostsStatus = $count > $limit ? 'status-overlimit' : 'status-ok';
    $hostsCell = (new CCol(
        (new CSpan((string) $count))->addClass($hostsStatus)
    ))->addClass('hosttracker-number-cell');

    $row = (new CRow([
        (new CCol($nameContent))
            ->addClass('hosttracker-company-cell')
            ->addStyle('padding-left: ' . (8 + ($depth * 24)) . 'px;'),
        $hostsCell,
        (new CCol((string) $limit))->addClass('hosttracker-number-cell'),
        (new CCol($input))->addClass('hosttracker-number-cell')
    ]))
        ->setId('hosttracker-limit-row-' . $groupId)
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

$form->addItem($table);

$buttons = (new CDiv())
    ->addClass('hosttracker-form-buttons')
    ->addItem([
        (new CSubmit('submit', _('Salvar Configurações')))
            ->addClass(ZBX_STYLE_BTN_ALT)
            ->setAttribute('type', 'submit'),
        ' ',
        (new CButton('cancel', _('Cancelar')))
            ->onClick('window.location.href="zabbix.php?action=hosttracker.view";')
    ]);

$form->addItem($buttons);
$widget->addItem($form);

$stats = isset($data['stats']) ? $data['stats'] : [];
$totalGroups = isset($stats['total_groups']) ? (int) $stats['total_groups'] : count($data['groups']);

$help = (new CDiv())
    ->addClass('info-container')
    ->addItem([
        (new CTag('h4', true, _('Informações:')))->addClass('hosttracker-info-title'),
        (new CList([
            _('Os limites continuam individuais para o grupo pai e para cada subgrupo.'),
            _('Grupos em vermelho estão acima do limite contratado.'),
            _('As configurações são salvas no arquivo limits.json do módulo.'),
            sprintf(_('Total de grupos configuráveis: %1$d'), $totalGroups)
        ]))
    ]);

$widget->addItem($help);
$widget->show();
