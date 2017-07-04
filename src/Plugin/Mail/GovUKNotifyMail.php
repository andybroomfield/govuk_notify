<?php

namespace Drupal\govuk_notify\Plugin\Mail;

use Http\Adapter\Guzzle6\Client;
use Drupal\Core\Mail\MailInterface;
use Alphagov\Notifications\Client as AlphagovClient;

/**
 * Defines the GovUK Notify mail backend.
 *
 * @Mail(
 *   id = "govuk_notify_mail",
 *   label = @Translation("GOV UK Notify mailer"),
 *   description = @Translation("Sends an email using the GOV UK Notify service.")
 * )
 */
class GovUKNotifyMail implements MailInterface {

  private $notifyClient;

  /**
   * Create the GovUK notify API client.
   */
  public function __construct() {
    $config = \Drupal::config('govuk_notify.settings');
    $api_key = $config->get('api_key');
    $this->notifyClient = new AlphagovClient([
      'apiKey' => $api_key,
      'httpClient' => new Client(),
    ]);
  }

  /**
   * {@inheritdoc}
   *
   * Ensures that the message contains the required parameters, namely
   * template_id - the template id to use
   * params - to contain the placeholders.
   */
  public function format(array $message) {
    $config = \Drupal::config('govuk_notify.settings');

    if (empty($message['template_id'])) {
      $message['template_id'] = $config->get('default_template_id');
    }

    if (empty($message['params'])) {
      $message['params'] = [];
    }

    // @todo Duplicate for permanent failure.
    // @todo Get email address from config.
    if ($config->get('force_temporary_failure')) {
      $temporary_email = 'temp-fail@simulator.notify';
      \Drupal::logger('govuk_notify')->notice('Forcing use of email address @email', ['@email' => $temporary_email]);
      $message['to'] = $temporary_email;
    }

    return $message;
  }

  /**
   * {@inheritdoc}
   *
   * This submits (NB not necessarily the same as send) a message to GovUK
   * Notify.
   */
  public function mail(array $message) {
    $response = NULL;

    // First, attempt to send the email.
    try {

      if (empty($message['to']) || empty($message['template_id']) || empty($message['params'])) {
        throw new Exception("message is missing one of the required parameters - to, template_id or params. " . print_r($message, 1));
      }

      $response = $this->notifyClient->sendEmail(
        $message['to'],
        $message['template_id'],
        $message['params']
      );

    }
    catch (Exception $e) {
      \Drupal::logger('govuk_notify')->notice("Failed to submit message to GovUK Notify: " . $e->getMessage());
      return FALSE;
    }
    catch (NotifyException $e) {
      \Drupal::logger('govuk_notify')->notice("Failed to submit message to GovUK Notify: " . $e->getMessage());
      return FALSE;
    }

    return TRUE;
  }

}
