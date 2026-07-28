<?php
namespace Modules\HostMonitoringTracker\Actions;

use CController;
use CControllerResponseData;
use CWebUser;

require_once dirname(__DIR__) . '/includes/HostTrackerDataService.php';

use Modules\HostMonitoringTracker\Includes\HostTrackerDataService;

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
        $dataset = (new HostTrackerDataService())->getHierarchyData();
        $userType = CWebUser::getType();

        $data = [
            'companies' => $dataset['rows'],
            'stats' => $dataset['stats'],
            'canManageLimits' => (
                $userType == USER_TYPE_ZABBIX_ADMIN
                || $userType == USER_TYPE_SUPER_ADMIN
            )
        ];

        $response = new CControllerResponseData($data);
        $response->setTitle(_('Acompanhamento de Host'));
        $this->setResponse($response);
    }
}
