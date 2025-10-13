<?php
/**
 * @var CView $this
 * @var array $data
 */

$widget = (new CHtmlPage())
    ->setTitle(_('Gerenciar Limites de Hosts'));

$form = (new CForm())
    ->setId('limits_form')
    ->setName('limits_form')
    ->setAction('zabbix.php?action=hosttracker.edit')
    ->setMethod('post');

$form->addItem(new CInput('hidden', 'save', '1'));

$table = (new CTable())
    ->setHeader([
        (new CColHeader(_('Grupo/Empresa')))->addStyle('text-align: left;'),
        (new CColHeader(_('Hosts Monitorados')))->addStyle('text-align: center;'),
        (new CColHeader(_('Limite Atual')))->addStyle('text-align: center;'),
        (new CColHeader(_('Novo Limite')))->addStyle('text-align: center;')
    ])
    ->addClass(ZBX_STYLE_LIST_TABLE);

if (!empty($data['groups'])) {
    foreach ($data['groups'] as $group) {
        $gid = $group['groupid'];
        $name = $group['name'];
        $limit = $group['current_limit'];
        $count = $group['hosts_count'];
        
        $input = (new CTextBox('limits[' . $gid . ']', $limit))
            ->setWidth(ZBX_TEXTAREA_SMALL_WIDTH)
            ->setAttribute('type', 'number')
            ->setAttribute('min', '0')
            ->setAttribute('max', '99999');
        
        if ($count > $limit) {
            $hostsCell = (new CCol($count . ' ⚠️'))
                ->addClass(ZBX_STYLE_RED)
                ->addStyle('text-align: center; font-weight: bold;');
        } else {
            $hostsCell = (new CCol($count . ' ✅'))
                ->addClass(ZBX_STYLE_GREEN)
                ->addStyle('text-align: center;');
        }
        
        $table->addRow([
            (new CCol($name))->addStyle('text-align: left;'),
            $hostsCell,
            (new CCol($limit))->addStyle('text-align: center;'),
            (new CCol($input))->addStyle('text-align: center;')
        ]);
    }
}

$form->addItem($table);

$buttons = (new CDiv())
    ->addStyle('margin-top: 15px;')
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

$help = (new CDiv())
    ->addStyle('margin-top: 20px; padding: 15px; background-color: #f0f8ff; border-left: 4px solid #0275b8; border-radius: 4px;')
    ->addItem([
        (new CTag('h4', false, '📋 ' . _('Informações:')))->addStyle('margin-top: 0; color: #0275b8;'),
        (new CList([
            '✅ ' . _('Grupos com checkmark verde estão dentro do limite'),
            '⚠️ ' . _('Grupos com aviso vermelho estão acima do limite contratado'),
            '💾 ' . _('As configurações são salvas em arquivo JSON no servidor'),
            '🔄 ' . _('Após salvar, volte para a tela principal para ver os novos status')
        ]))
    ]);

$widget->addItem($help);

$widget->show();
