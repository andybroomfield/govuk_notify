<?php
 
namespace Drupal\govuk_notify\Plugin\Mail;
 
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
 
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
 
  private $notify_client;
 
  public function __construct() {
    $config = \Drupal::config('govuk_notify.settings');  
    $api_key = $config->get('api_key');
    \Drupal::logger('govuk_notify')->notice("Using api key {$api_key}");
    $this->notify_client = new \Alphagov\Notifications\Client([
      'apiKey' => $api_key,
      'httpClient' => new \Http\Adapter\Guzzle6\Client
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    $config = \Drupal::config('govuk_notify.settings');  
    if (empty($message['template_id'])) {
      $message['template_id'] = $config->get('default_template_id');
    }
    \Drupal::logger('govuk_notify')->notice(print_r($message,1));
    return $message;
  }
 
  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    try {
      $response = $this->notify_client->sendEmail(
        $message['to'],
        $message['template_id'],
        $message['params']);
      return $response; 
    }
    catch (NotifyException $e){
      \Drupal::logger('govuk_notify')->notice("Failed to send message: " . $e->getMessage());
      return false;
    }
  }
}
