<?php

namespace Civi\K2bQrcode;

use Civi\Api4\Contact;
use Civi\Api4\Participant;
use Civi\Api4\Relationship;
use CRM_K2bQrcode_ExtensionUtil as E;

class Qrcode {

  /**
   * Create the QRCode image file
   *
   * @param int $participantID
   * @param array $qrcodeValues
   *
   * @return void
   */
  public static function createQrcodeImage(int $participantID, array $qrcodeValues, bool $regenerate = FALSE) {
    // Format: <CurrentYear><Team Number><ContactID of the individual>
    $qrcodeStr = $qrcodeValues['currentYear'] . ';' . $qrcodeValues['teamNumber'] . ';' . $qrcodeValues['contactId'];
    $path = qrcodecheckin_get_path($qrcodeValues['filename']);

    if ($regenerate && file_exists($path)) {
      // Delete the existing file
      unlink($path);
    }

    if (!file_exists($path)) {
      // Since we are saving a file, we don't want base64 data.
      //$url = qrcodecheckin_get_url($filename, $participant_id);
      $base64 = FALSE;
      $data = qrcodecheckin_get_image_data($qrcodeStr, $base64);
      file_put_contents($path, $data);
      Participant::update(FALSE)
        ->addValue('QRCode.QRCode_Public_link', qrcodecheckin_get_image_url($qrcodeValues['filename']))
        ->addWhere('id', '=', $participantID)
        ->execute();
    }
  }

  /**
   * Get the values for the QRCode
   * @param int $contactID
   * @param int $participantID
   *
   * @return array
   * @throws \CRM_Core_Exception
   */
  public static function getQrcodeValues(int $contactID, int $participantID) {
    // Format: <CurrentYear><Team Number><ContactID of the individual>
    // <Team Number> is a custom field on a related contact:
    //   - Relationship is 'Team Member For'.
    //   - Teams are actually Households but have been re-named.
    //   - Needs to be a Team not in trash.

    // We get the most recent event with "bus" in the title to give us the year.
    $event = \Civi\Api4\Event::get(FALSE)
      ->addSelect('start_date')
      ->addWhere('title', 'CONTAINS', 'bus')
      ->addOrderBy('start_date', 'DESC')
      ->execute()
      ->first();
    if (empty($event)) {
      $currentYear = date('Y');
    }
    else {
      $currentYear = date('Y', strtotime($event['start_date']));
    }

    $relationship = Relationship::get(FALSE)
      ->addSelect('contact_id_b', 'contact_id_b.Team_Type.Team_Name_manually_set_')
      ->addWhere('relationship_type_id:name', '=', 'Employee of')
      ->addWhere('contact_id_a', '=', $contactID)
      ->addWhere('is_active', '=', TRUE)
      ->addWhere('contact_id_b.is_deleted', '=', FALSE)
      ->execute()
      ->first();
    $teamNumber = $relationship['contact_id_b.Team_Type.Team_Name_manually_set_'] ?? 'NOTEAMNUMBER';

    $contactDetails = Contact::get(FALSE)
      ->addSelect('display_name', 'hash')
      ->addWhere('id', '=', $contactID)
      ->execute()
      ->first();

    $codeValues = [
      'contactId' => $contactID,
      'displayName' => $contactDetails['display_name'],
      'participantId' => $participantID,
      'currentYear' => $currentYear,
      'teamNumber' => $teamNumber,
      'filename' => hash('sha256', $participantID . $contactDetails['hash'] . CIVICRM_SITE_KEY),
    ];

    // For testing:
    // $codeValues['currentYear'] = '2025';
    // $codeValues['teamNumber'] = '1234';

    return $codeValues;
  }

}