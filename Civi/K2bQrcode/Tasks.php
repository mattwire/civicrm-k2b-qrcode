<?php

namespace Civi\K2bQrcode;

use Civi\Core\Service\AutoSubscriber;
use CRM_K2bQrcode_ExtensionUtil as E;

class Tasks extends AutoSubscriber {

  public static function getSubscribedEvents() {
    return [
      '&hook_civicrm_searchKitTasks' => 'onSearchKitTasks',
    ];
  }

  public function onSearchKitTasks(array &$tasks, bool $checkPermissions, ?int $userID): void {
    $tasks['Participant']['generatek2bcode'] = [
      'title' => E::ts('Generate K2B QRCode'),
      'icon' => 'fa-rectangle-refresh',
      'apiBatch' => [
        'action' => 'GenerateK2bcode',
        'params' => NULL,
        'confirmMsg' => E::ts('Generate %1 QRCodes for %2.'),
        'runMsg' => E::ts('Generating %1 QRCodes for %2...'),
        'successMsg' => E::ts('%1 QRCodes have been generated for %2.'),
        'errorMsg' => E::ts('An error occurred while attempting to generate %1 QRCodes for %2.'),
      ],
    ];
  }

}
