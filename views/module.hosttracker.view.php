<?php
/**
 * @var CView $this
 * @var array $data
 */

// Criar widget principal com botões lado a lado
$widget = (new CHtmlPage())
    ->setTitle(_('Acompanhamento de Host'))
    ->setControls(
        (new CList([
            (new CButton('manage_limits', _('Gerenciar Limites')))
                ->addClass(ZBX_STYLE_BTN_ALT)
                ->setAttribute('onclick', 'window.location.href="zabbix.php?action=hosttracker.edit"'),
            (new CButton('export_pdf', _('Gerar PDF')))
                ->addClass(ZBX_STYLE_BTN_ALT)
                ->setAttribute('onclick', 'window.location.href="zabbix.php?action=hosttracker.pdf"')
        ]))
    );

// Criar tabela com dados
$table = (new CTableInfo())
    ->setHeader([
        _('Empresa'),
        _('Contratado'),
        _('Atual'),
        _('Status')
    ]);

// Verificar se há dados
if (empty($data['companies'])) {
    $table->addRow([
        (new CCol(_('Nenhuma empresa encontrada')))
            ->setColSpan(4)
            ->addClass(ZBX_STYLE_GREY)
    ]);
} else {
    // Adicionar linhas para cada empresa
    foreach ($data['companies'] as $company) {
        // Criar célula de status com cor
        if ($company['status'] === 'OK') {
            $statusCell = (new CSpan($company['status']))
                ->addClass(ZBX_STYLE_GREEN);
        } else {
            $statusCell = (new CSpan($company['status']))
                ->addClass(ZBX_STYLE_RED);
        }
        
        // Adicionar cor na célula "Atual" se estiver acima do limite
        $currentValue = $company['current'];
        if ($company['status'] === 'Acima do Limite') {
            $currentCell = (new CSpan($currentValue))
                ->addClass(ZBX_STYLE_RED)
                ->addStyle('font-weight: bold;');
        } else {
            $currentCell = new CCol($currentValue);
        }
        
        $table->addRow([
            new CCol($company['name']),
            new CCol($company['contracted']),
            $currentCell,
            $statusCell
        ]);
    }
}

// Adicionar tabela ao widget
$widget->addItem($table);

// Adicionar informações adicionais
$infoDiv = (new CDiv())
    ->addStyle('margin-top: 15px; padding: 10px 15px; background-color: #f5f5f5; border-left: 4px solid #0275b8; border-radius: 4px;')
    ->addItem(
        (new CSpan())
            ->addItem([
                (new CTag('strong', false, _('Total de Empresas: '))),
                count($data['companies'])
            ])
    );

$widget->addItem($infoDiv);

// Exibir widget
$widget->show();
