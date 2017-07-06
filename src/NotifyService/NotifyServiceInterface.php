<?php

namespace Drupal\govuk_notify\NotifyService;

/**
 *
 */
interface NotifyServiceInterface {

  /**
   *
   */
  public function __construct();

  /**
   *
   */
  public function sendEmail($to, $template_id, $params);

  /**
   *
   */
  public function listNotifications($filter = []);

}
