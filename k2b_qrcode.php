<?php

require_once 'k2b_qrcode.civix.php';
use CRM_K2bQrcode_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function k2b_qrcode_civicrm_config(&$config) {
  _k2b_qrcode_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function k2b_qrcode_civicrm_install() {
  _k2b_qrcode_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function k2b_qrcode_civicrm_enable() {
  _k2b_qrcode_civix_civicrm_enable();
}

function k2b_qrcode_civicrm_qrcodecheckin_tokenValues(&$values, $contact_id, &$handled) {
  // It would be best if we had token event ids passed on from the hook params, but we don't.
  // For now lets use the config to determine event ids.
  $qrcode_events = \Civi::settings()->get('qrcode_events');
  foreach ($qrcode_events as $event_id) {
    $participant_id = qrcodecheckin_participant_id_for_contact_id($contact_id, $event_id);
    if ($participant_id) {
      $qrcodeValues = k2b_qrcode_getValues($contact_id, $participant_id);

      // First ensure the image file is created.
      k2b_qrcode_create_image($qrcodeValues);

      // Get the absolute link to the image that will display the QR code.
      $link = qrcodecheckin_get_image_url($qrcodeValues['filename']);

      $emailMessage = "" .
        "<div><p>Your Bus pickup details are:</p>" .
        "<ul><li>Time: {$qrcodeValues['busTime']}</li><li>Location: {$qrcodeValues['busPickup']}</li></ul>" .
        "</div>";

      $values['qrcodecheckin.qrcode_url_' . $event_id] = $link;
      $values['qrcodecheckin.qrcode_html_' . $event_id] = '<div><p>Please show the QR code below to your bus driver:</p>' .
        '<img alt="QR Code with participant details" src="' . $link .
        '"></div><div>If you do not see a code display above, ' .
        'please enable the display of images in your email ' .
        'program or try accessing it <a href="' . $link . '">directly</a>. ' .
        'You may want to take a screen grab of your QR Code in case you need ' .
        'to display it when you do not have Internet access.</div>' .
        '<br />' . $emailMessage;
    }
    $handled = TRUE;
  }
}

function k2b_qrcode_getValues($contactId, $participantId) {
  $busPickupField = 'custom_11';
  $busTimeField = 'custom_139';
  $contactParams = [
    'id' => $contactId,
    'return' => ['display_name', 'hash', $busPickupField, $busTimeField],
  ];
  $contactDetails = civicrm_api3('Contact', 'getsingle', $contactParams);
  $busTime = $contactDetails[$busTimeField];

  // Get the bus pick location (we need to look this up from a select list
  // FIXME: Replace with CustomValue.Getdisplayvalue when https://github.com/civicrm/civicrm-core/pull/13365 is merged
  $busPickupParts = explode('_', $busPickupField);
  $busPickupFieldId = CRM_Utils_Array::value(1, $busPickupParts);
  $busPickup = NULL;
  if (!empty($busPickupFieldId)) {
    $busPickup = CRM_Core_BAO_CustomField::displayValue($contactDetails[$busPickupField], $busPickupFieldId, $contactId);
  }

  $codeValues = [
    'contactId' => $contactId,
    'displayName' => $contactDetails['display_name'],
    'participantId' => $participantId,
    'busTime' => $busTime,
    'busPickup' => $busPickup,
    'filename' => hash('sha256', $participantId . $contactDetails['hash'] . CIVICRM_SITE_KEY),
  ];

  // For testing:
  // $codeValues['busTime'] = '12:00';
  // $codeValues['busPickup'] = 'Moon, the dark side';

  return $codeValues;
}

/**
 * Create the qr image file
 */
function k2b_qrcode_create_image($qrcodeValues) {
  $qrcodeStr = $qrcodeValues['contactId'] . ';' . $qrcodeValues['busTime'] . ';' . $qrcodeValues['busPickup'];
  $path = qrcodecheckin_get_path($qrcodeValues['filename']);

  if (!file_exists($path)) {
    // Since we are saving a file, we don't want base64 data.
    //$url = qrcodecheckin_get_url($filename, $participant_id);
    $base64 = FALSE;
    $data = qrcodecheckin_get_image_data($qrcodeStr, $base64);
    file_put_contents($path, $data);
  }
}
