<?php
namespace Modules\HostMonitoringTracker;

use Zabbix\Core\CModule;
use APP;
use CMenuItem;

class Module extends CModule {
    
    /**
     * Inicializa o módulo
     */
    public function init(): void {
        // Adicionar item ao menu Monitoring
        APP::Component()->get('menu.main')
            ->findOrAdd(_('Monitoring'))
            ->getSubmenu()
            ->add(
                (new CMenuItem(_('Acompanhamento de Host')))
                    ->setAction('hosttracker.view')
            );
    }
}
