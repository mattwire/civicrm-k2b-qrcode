<?php

namespace Civi\Api4\Action\Participant;

use Civi\Api4\Participant;
use Civi\K2bQrcode\Qrcode;
use CRM_K2bQrcode_ExtensionUtil as E;
use Civi\Api4\Generic\Result;

/**
 * Generate a QRCode and store link in Participant custom field
 */
class GenerateK2bcode extends \Civi\Api4\Generic\AbstractBatchAction {

  /**
   * If set to TRUE, will delete and regenerate QRCode if it exists
   * @var bool
   */
  protected bool $regenerate = FALSE;

  /**
   * Every action must define a _run function to perform the work and place results in the Result object.
   *
   * When using the set of Basic actions, they define _run for you and you just need to provide a getter/setter function.
   *
   * @param Result $result
   */
  public function _run(Result $result) {
    $records = $this->getBatchRecords();

    foreach ($records as $record) {
      $participantID = $record['id'];
      $contactID = $record['contact_id'];

      if (empty($contactID)) {
        throw new \CRM_Core_Exception('Could not get Contact ID from Participant ID');
      }

      $qrcodeValues = Qrcode::getQrcodeValues($contactID, $participantID);

      // First ensure the image file is created.
      Qrcode::createQrcodeImage($participantID, $qrcodeValues, $this->regenerate);
      $results[] = [
        'url' => qrcodecheckin_get_image_url($qrcodeValues['filename']),
      ];
    }

    $result->exchangeArray($results ?? []);
  }

  /**
   * Determines what fields will be returned by getBatchRecords
   *
   * Defaults to an entity's primary key(s), typically ['id']
   *
   * @return string[]
   */
  protected function getSelect() {
    return ['contact_id'];
  }

}
