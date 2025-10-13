<?php
namespace Modules\HostMonitoringTracker\Actions;

use CController;
use CControllerResponseData;
use API;

class HostTrackerView extends CController {
    
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
        $companies = $this->getCompaniesData();
        $data = ['companies' => $companies];
        $response = new CControllerResponseData($data);
        $response->setTitle('Acompanhamento de Host');
        $this->setResponse($response);
    }
    
    private function getCompaniesData() {
        $result = [];
        
        try {
            $groups = API::HostGroup()->get(['output' => ['groupid', 'name'], 'sortfield' => 'name']);
            
            if (!$groups) return [];
            
            foreach ($groups as $g) {
                $limit = $this->getLimit($g['groupid']);
                $count = API::Host()->get(['groupids' => $g['groupid'], 'filter' => ['status' => 0], 'countOutput' => true]);
                
                $result[] = [
                    'groupid' => $g['groupid'],
                    'name' => $g['name'],
                    'contracted' => $limit,
                    'current' => (int)$count,
                    'status' => ((int)$count > $limit) ? 'Acima do Limite' : 'OK'
                ];
            }
        } catch (\Exception $e) {
            error_log('Erro: ' . $e->getMessage());
        }
        
        return $result;
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
