<?php

namespace Drupal\govuk_notify\Plugin\Mail;

use Http\Adapter\Guzzle6\Client;
use Drupal\Core\Mail\MailInterface;

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
    $this->notifyClient = new \Alphagov\Notifications\Client([
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

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    $response = NULL;

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
      \Drupal::logger('govuk_notify')->notice("Failed to send message: " . $e->getMessage());
    }
    catch (NotifyException $e) {
      \Drupal::logger('govuk_notify')->notice("Failed to send message: " . $e->getMessage());
    }

    return $response;
  }

}
