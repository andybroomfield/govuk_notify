<?php

namespace Drupal\govuk_notify\Plugin\Mail;

use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\govuk_notify\NotifyService\NotifyServiceInterface;

/**
 * Defines the GovUK Notify mail backend.
 *
 * @Mail(
 *   id = "govuk_notify_mail",
 *   label = @Translation("GOV UK Notify mailer"),
 *   description = @Translation("Sends an email using the GOV UK Notify service.")
 * )
 */
class GovUKNotifyMail implements MailInterface, ContainerFactoryPluginInterface {

  private $notifyService;

  /**
   * Create the GovUK notify API client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, NotifyServiceInterface $notify_service) {
    $this->notifyService = $notify_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('govuk_notify.notify_service')
    );
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
    $response = FALSE;;

    if (!empty($message['to']) || !empty($message['template_id']) || !empty($message['params'])) {
      $response = $this->notifyService->sendEmail(
        $message['to'],
        $message['template_id'],
        $message['params']
      );
    }
    else {
      \Drupal::logger('govuk_notify')->warning('Message was missing one of the required parameters - to, template_id or params');
    }

    return $response;

  }

}
