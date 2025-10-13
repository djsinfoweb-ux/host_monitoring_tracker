/**
 * JavaScript para o módulo de Acompanhamento de Host
 */

// Função para exportar para PDF
function exportToPDF() {
    // Mostrar mensagem de carregamento
    const overlay = overlayDialogue({
        'title': 'Gerando PDF',
        'content': 'Por favor, aguarde enquanto o PDF está sendo gerado...',
        'buttons': []
    });
    
    // Redirecionar para geração de PDF
    window.location.href = 'zabbix.php?action=hosttracker.pdf';
    
    // Fechar overlay após um tempo
    setTimeout(() => {
        overlayDialogueDestroy('overlay_dialogue');
    }, 2000);
}

// Adicionar tooltips aos status
jQuery(document).ready(function($) {
    'use strict';
    
    // Adicionar tooltips explicativos
    $('.status-ok').attr('title', 'Quantidade de hosts está dentro do limite contratado');
    $('.status-overlimit').attr('title', 'Quantidade de hosts excedeu o limite contratado');
    
    // Adicionar efeito visual ao passar o mouse
    $('table.list-table tbody tr').hover(
        function() {
            $(this).css('background-color', '#F0F8FF');
        },
        function() {
            $(this).css('background-color', '');
        }
    );
});
