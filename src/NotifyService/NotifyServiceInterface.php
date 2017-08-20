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
   * Send an email via Notify.
   *
   * @return mixed
   */
  public function sendEmail($to, $template_id, $params);

  /**
   * Load a template from Notify.
   *
   * @param string $template_id
   *   A template ID to load.
   *
   * @return mixed|False
   *   An array of template information from notify or FALSE if none exists.
   */
  public function getTemplate($template_id);

  /**
   * Check whether a template component has a replacement variable.
   *
   * @param string $component
   *   The template component to search in.
   * @param string $replacement
   *   The replacement token to check for.
   *
   * @return boolean
   *   TRUE if the token exists in the component.
   */
  public function checkReplacement($component, $replacement);

  /**
   * Returns a list of all notifications for the current Service ID.
   */
  public function listNotifications($filter = []);

}
