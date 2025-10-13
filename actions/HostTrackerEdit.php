<?php
namespace Modules\HostMonitoringTracker\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseRedirect;
use CUrl;
use API;

class HostTrackerEdit extends CController {
    
    protected function init() {
        $this->disableCsrfValidation();
    }
    
    protected function checkInput() {
        $fields = [
            'save' => 'in 1',
            'limits' => 'array'
        ];
        
        return $this->validateInput($fields);
    }
    
    protected function checkPermissions() {
        return true;
    }
    
    protected function doAction() {
        if ($this->hasInput('save')) {
            // Salvando
            $limits = $this->getInput('limits', []);
            $file = '/usr/share/zabbix/modules/host_monitoring_tracker/limits.json';
            
            if (!empty($limits)) {
                $json = json_encode($limits, JSON_PRETTY_PRINT);
                $result = file_put_contents($file, $json);
                
                if ($result !== false) {
                    @chmod($file, 0666);
                    info(_('Limites salvos com sucesso!'));
                } else {
                    error(_('Erro ao salvar limites.'));
                }
            }
            
            // Redirecionar
            $response = new CControllerResponseRedirect((new CUrl('zabbix.php'))->setArgument('action', 'hosttracker.view'));
            $this->setResponse($response);
            return;
        }
        
        // Exibindo formulário
        $groups = [];
        
        try {
            $hg = API::HostGroup()->get(['output' => ['groupid', 'name'], 'sortfield' => 'name']);
            
            foreach ($hg as $g) {
                $gid = $g['groupid'];
                $lim = $this->getLimit($gid);
                $cnt = API::Host()->get(['groupids' => $gid, 'filter' => ['status' => 0], 'countOutput' => true]);
                
                $groups[] = [
                    'groupid' => $gid,
                    'name' => $g['name'],
                    'current_limit' => $lim,
                    'hosts_count' => (int)$cnt
                ];
            }
        } catch (\Exception $e) {
            error_log('Erro: ' . $e->getMessage());
        }
        
        $data = ['groups' => $groups];
        $response = new CControllerResponseData($data);
        $response->setTitle(_('Gerenciar Limites de Hosts'));
        $this->setResponse($response);
    }
    
    private function getLimit($gid) {
        $f = '/usr/share/zabbix/modules/host_monitoring_tracker/limits.json';
        if (file_exists($f)) {
            $c = @file_get_contents($f);
            if ($c && $c !== '{}') {
                $d = @json_decode($c, true);
                if ($d && isset($d[$gid])) return (int)$d[$gid];
            }
        }
        return 100;
    }
}
