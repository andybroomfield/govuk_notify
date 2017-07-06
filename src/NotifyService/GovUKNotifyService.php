<?php

namespace Drupal\govuk_notify\NotifyService;

use Http\Adapter\Guzzle6\Client;
use Alphagov\Notifications\Client as AlphagovClient;

/**
 * 
 */
class GovUKNotifyService implements NotifyServiceInterface {

  protected $notifyClient = NULL;

  /**
   * Create the GovUK notify API client.
   */
  public function __construct() {
    $config = \Drupal::config('govuk_notify.settings');
    try {
      $this->notifyClient = new AlphagovClient([
        'apiKey' => $config->get('api_key'),
        'httpClient' => new Client(),
      ]);
    }
    catch (\Alphagov\Notifications\Exception\ApiException $e) {
      \Drupal::logger('govuk_notify')->warning("Failed to create GovUK Notify Client using API: @message",
        ['@message' => $e->getMessage()]);
    }
    catch (Exception $e) {
      \Drupal::logger('govuk_notify')->warning("Failed to create GovUK Notify Client using API: @message",
        ['@message' => $e->getMessage()]);
    }
  }

  /**
   *
   */
  public function sendEmail($to, $template_id, $params) {

    try {
      return $this->notifyClient->sendEmail($to, $template_id, $params);
    }
    catch (\Alphagov\Notifications\Exception\ApiException $e) {
      \Drupal::logger('govuk_notify')->warning("Failed to send email using API: @message",
        ['@message' => $e->getMessage()]);
    }
    catch (Exception $e) {
      \Drupal::logger('govuk_notify')->warning("Failed to send email using API: @message",
        ['@message' => $e->getMessage()]);
    }
    return FALSE;
  }

  /**
   *
   */
  public function listNotifications($filter = []) {
    return $this->notifyClient->listNotifications($filter);
  }

}
