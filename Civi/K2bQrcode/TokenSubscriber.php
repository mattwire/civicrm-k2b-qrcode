<?php

namespace Civi\K2bQrcode;

use CRM_K2bQrcode_ExtensionUtil as E;
use Civi\Core\Service\AutoService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @service
 * @internal
 */
class TokenSubscriber extends AutoService implements EventSubscriberInterface {

  /**
   * @var string
   */
  private static string $entityName = 'qrcodecheckin';

  public static function getSubscribedEvents(): array {
    return [
      // 'civi.token.list' => 'registerTokens',
      'civi.token.eval' => 'evaluateTokens',
    ];
  }

  /**
   * Expose tokens for use in UI.
   *
   * @param \Civi\Core\Event\GenericHookEvent $e
   * @see \CRM_Utils_Hook::tokens()
   */
  public static function registerTokens(\Civi\Token\Event\TokenRegisterEvent $event) {
    // qrcodecheckin extension does this in qrcodecheckin_civicrm_tokens
    // $event->entity('qrcodecheckin')
    //  ->register('qrcode_url_' . $event['id'], E::ts('QRCode link for event ') . $event['title'])
    //  ->register('qrcode_html_', $event['id'], E::ts('QRCode image and link for event ') . $event['title']);
  }

  /**
   * Substitute any tokens with actual values.
   *
   * @param \Civi\Core\Event\GenericHookEvent $e
   * @see \CRM_Utils_Hook::tokenValues()
   */
  public static function evaluateTokens(\Civi\Token\Event\TokenValueEvent $event) {
    foreach ($event->getRows() as $row) {
      /** @var \Civi\Token\TokenRow $row */
      $row->format('text/html');

      $contactID = $row->context['contactId'];
      if (empty($contactID)) {
        \Civi::log()->debug('Not processing qrcode tokens because contactId context is not set');
        continue;
      }
      $eventID = $row->context['eventId'];
      if (empty($eventID)) {
        \Civi::log()->debug('Not processing qrcode tokens because eventId context is not set');
        continue;
      }
      $participantID = $row->context['participantId'] ?? qrcodecheckin_participant_id_for_contact_id($contactID, $eventID);
      if (empty($participantID)) {
        \Civi::log()->debug('Not processing qrcode tokens because participantId context is not set and could not get from qrcodecheckin_participant_id_for_contact_id');
        continue;
      }

      $qrcodeValues = Qrcode::getQrcodeValues($contactID, $participantID);

      // First ensure the image file is created.
      Qrcode::createQrcodeImage($participantID, $qrcodeValues);

      // Get the absolute link to the image that will display the QR code.
      $link = qrcodecheckin_get_image_url($qrcodeValues['filename']);

      $row->tokens(
        self::$entityName,
        'qrcode_url_' . $eventID,
        $link
      );
      $row->tokens(
        self::$entityName,
        'qrcode_html_' . $eventID,
        '<div></div><img alt="QR Code with participant details" src="' . $link .
        '"></div><div>If you do not see a code display above, ' .
        'please enable the display of images in your email ' .
        'program or try accessing it <a href="' . $link . '">directly</a>. ' .
        'You may want to take a screen grab of your QR Code in case you need ' .
        'to display it when you do not have Internet access.</div>' .
        '<br />'
      );
    }
  }

}
