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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function k2b_qrcode_civicrm_xmlMenu(&$files) {
  _k2b_qrcode_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function k2b_qrcode_civicrm_postInstall() {
  _k2b_qrcode_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function k2b_qrcode_civicrm_uninstall() {
  _k2b_qrcode_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function k2b_qrcode_civicrm_enable() {
  _k2b_qrcode_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function k2b_qrcode_civicrm_disable() {
  _k2b_qrcode_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function k2b_qrcode_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _k2b_qrcode_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function k2b_qrcode_civicrm_managed(&$entities) {
  _k2b_qrcode_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function k2b_qrcode_civicrm_caseTypes(&$caseTypes) {
  _k2b_qrcode_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function k2b_qrcode_civicrm_angularModules(&$angularModules) {
  _k2b_qrcode_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function k2b_qrcode_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _k2b_qrcode_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function k2b_qrcode_civicrm_entityTypes(&$entityTypes) {
  _k2b_qrcode_civix_civicrm_entityTypes($entityTypes);
}

function k2b_qrcode_civicrm_qrcodecheckin_tokenValues(&$values, $contact_id, &$handled) {
  $participant_id = qrcodecheckin_participant_id_for_contact_id($contact_id);
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

    $values['qrcodecheckin.qrcode_url'] = $link;
    $values['qrcodecheckin.qrcode_html'] = '<div><p>Please show the QR code below to your bus driver:</p>' .
      '<img alt="QR Code with participant details" src="' . $link .
      '"></div><div>If you do not see a code display above, ' .
      'please enable the display of images in your email ' .
      'program or try accessing it <a href="' . $link . '">directly</a>. '.
      'You may want to take a screen grab of your QR Code in case you need '.
      'to display it when you do not have Internet access.</div>' .
      '<br />' . $emailMessage;
  }
  $handled = TRUE;
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
  list($_, $busPickupFieldId, $_) = explode('_', $busPickupField);
  $busPickup = CRM_Core_BAO_CustomField::displayValue($contactDetails[$busPickupField], $busPickupFieldId, $contactId);

  $codeValues = [
    'contactId' => $contactId,
    'displayName' => $contactDetails['display_name'],
    'participantId' => $participantId,
    'busTime' => $busTime,
    'busPickup' => $busPickup,
    'filename' => hash('sha256', $participantId + $contactDetails['hash'] + CIVICRM_SITE_KEY),
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
