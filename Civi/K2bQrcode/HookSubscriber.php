<?php

namespace Civi\K2bQrcode;

use Civi\Api4\Participant;
use CRM_K2bQrcode_ExtensionUtil as E;
use Civi\Core\Service\AutoService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @service
 * @internal
 */
class HookSubscriber extends AutoService implements EventSubscriberInterface {

  public static function getSubscribedEvents(): array {
    return [
      '&hook_civicrm_postCommit' => [['onPostCommit', 100]],
    ];
  }

  /**
   * Implements hook_civicrm_postCommit().
   *
   * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postCommit
   */
  public static function onPostCommit($op, $objectName, $objectId, &$objectRef) {
    if (!($objectName === 'Participant' && in_array($op, ['create', 'edit']))) {
      return;
    }

    if (empty($objectId)) {
      return;
    }

    // Check if Participant has paid
    $participant = Participant::get(FALSE)
      ->addWhere('id', '=', $objectId)
      ->addWhere('status_id:name', 'IN', ['Registered', 'Attended', 'Transferred'])
      ->execute()
      ->first();

    if (empty($participant)) {
      return;
    }

    // Generate the QR Code image
    Participant::generateK2bcode(FALSE)
      ->addWhere('id', '=', $objectId)
      ->execute();
  }

}
