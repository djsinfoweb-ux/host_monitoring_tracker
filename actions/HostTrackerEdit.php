<?php
namespace Modules\HostMonitoringTracker\Actions;

use CController;
use CControllerResponseData;
use CControllerResponseRedirect;
use CUrl;
use CWebUser;
use CRoleHelper;

require_once dirname(__DIR__) . '/includes/HostTrackerDataService.php';

use Modules\HostMonitoringTracker\Includes\HostTrackerDataService;

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
        if (!$this->checkAccess(CRoleHelper::UI_ADMINISTRATION_GENERAL)) {
            return (
                CWebUser::getType() == USER_TYPE_ZABBIX_ADMIN
                || CWebUser::getType() == USER_TYPE_SUPER_ADMIN
            );
        }

        return true;
    }

    protected function doAction() {
        $service = new HostTrackerDataService();

        if ($this->hasInput('save')) {
            $limits = $this->getInput('limits', []);

            if ($service->saveLimits($limits)) {
                info(_('Limites salvos com sucesso!'));
            }
            else {
                error(_(
                    'Erro ao salvar limites. Verifique as permissões do arquivo limits.json.'
                ));
            }

            $response = new CControllerResponseRedirect(
                (new CUrl('zabbix.php'))->setArgument('action', 'hosttracker.view')
            );
            $this->setResponse($response);
            return;
        }

        $dataset = $service->getHierarchyData();

        $data = [
            'groups' => $dataset['rows'],
            'stats' => $dataset['stats']
        ];

        $response = new CControllerResponseData($data);
        $response->setTitle(_('Gerenciar Limites de Hosts'));
        $this->setResponse($response);
    }
}
