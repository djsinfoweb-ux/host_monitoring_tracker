<?php
namespace Modules\HostMonitoringTracker\Actions;

use CController;
use API;

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
        // Buscar empresas
        $companies = $this->getCompaniesData();
        
        // Gerar HTML
        $html = $this->generateHtml($companies);
        
        // Enviar como HTML (navegador pode salvar como PDF)
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="acompanhamento_hosts_' . date('Y-m-d') . '.html"');
        
        echo $html;
        exit;
    }
    
    /**
     * Busca dados das empresas
     */
    private function getCompaniesData() {
        $companies = [];
        
        try {
            $hostGroups = API::HostGroup()->get([
                'output' => ['groupid', 'name'],
                'sortfield' => 'name'
            ]);
            
            foreach ($hostGroups as $group) {
                $groupid = $group['groupid'];
                $companyName = $group['name'];
                
                $contractedLimit = $this->getContractedLimit($groupid);
                
                $activeHosts = API::Host()->get([
                    'groupids' => $groupid,
                    'filter' => ['status' => HOST_STATUS_MONITORED],
                    'countOutput' => true
                ]);
                
                $status = 'OK';
                if ($activeHosts > $contractedLimit) {
                    $status = 'Acima do Limite';
                }
                
                $companies[] = [
                    'name' => $companyName,
                    'contracted' => $contractedLimit,
                    'current' => (int)$activeHosts,
                    'status' => $status
                ];
            }
            
        } catch (Exception $e) {
            error_log('Erro PDF: ' . $e->getMessage());
        }
        
        return $companies;
    }
    
    /**
     * Busca limite contratado
     */
    private function getContractedLimit($groupid) {
        $configFile = '/usr/share/zabbix/modules/host_monitoring_tracker/limits.json';
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            if ($content) {
                $config = json_decode($content, true);
                if (isset($config[$groupid])) {
                    return (int)$config[$groupid];
                }
            }
        }
        return 100;
    }
    
    /**
     * Gera HTML para impressão/PDF
     */
    private function generateHtml($companies) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Acompanhamento de Host</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: white;
        }
        h1 {
            text-align: center;
            color: #333;
            border-bottom: 3px solid #0275b8;
            padding-bottom: 10px;
        }
        .info {
            margin: 20px 0;
            padding: 10px;
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
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-ok {
            color: #2E7D32;
            font-weight: bold;
        }
        .status-overlimit {
            color: #C62828;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
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
        <strong>Data de Geração:</strong> ' . date('d/m/Y H:i:s') . '<br>
        <strong>Total de Empresas:</strong> ' . count($companies) . '
    </div>
    
    <button class="no-print" onclick="window.print()" style="padding: 10px 20px; background: #0275b8; color: white; border: none; cursor: pointer; margin-bottom: 20px;">
        🖨️ Imprimir / Salvar como PDF
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
            $statusClass = ($company['status'] === 'OK') ? 'status-ok' : 'status-overlimit';
            
            $html .= '<tr>
                <td>' . htmlspecialchars($company['name']) . '</td>
                <td>' . $company['contracted'] . '</td>
                <td>' . $company['current'] . '</td>
                <td class="' . $statusClass . '">' . $company['status'] . '</td>
            </tr>';
        }
        
        $html .= '
        </tbody>
    </table>
    
    <div class="footer">
        <p>Documento gerado automaticamente pelo Sistema de Monitoramento Zabbix</p>
    </div>
    
    <script>
    // Auto-imprimir ao carregar (opcional)
    // window.onload = function() { window.print(); }
    </script>
</body>
</html>';
        
        return $html;
    }
}
