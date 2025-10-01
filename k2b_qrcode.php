<?php

require_once 'k2b_qrcode.civix.php';

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
