<?php

namespace Drupal\govuk_notify_views_backend\Plugin\views\query;

use Drupal\views\Plugin\views\query\QueryPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Alphagov\Notifications\Client as AlphagovClient;
use Http\Adapter\Guzzle6\Client;
use Drupal\views\ViewExecutable;
use Drupal\views\ResultRow;

/**
 * Queries GovUK notify.
 *
 * @ViewsQuery(
 *   id = "govuk_notify_views_backend",
 *   title = @Translation("GovUK Notify"),
 *   help = @Translation("Query against the GovUK Notify backend.")
 * )
 */
class GovUKNotifyMessages extends QueryPluginBase {

  private $notifyClient;

  /**
   * GovUK notify constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // @todo - put this into constructor fn declaration.
    $config = \Drupal::config('govuk_notify.settings');
    $api_key = $config->get('api_key');
    $this->notifyClient = new AlphagovClient([
      'apiKey' => $api_key,
      'httpClient' => new Client(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
      /* $container->get('govuk_notify.client'), */
      /* $container->get('govuk_notify.access_token_manager') */
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {

    $index = 0;

    try {

      $response = $this->notifyClient->listNotifications();

      foreach ($response['notifications'] as $notification) {
        foreach ($notification as $notification_key => $notification_value) {
          $row[$notification_key] = $notification_value;
        }
        /**
        $row['id'] = $notification['id'];
        $row['email_address'] = $notification['email_address'];
        */
        $row['index'] = $index++;
        $view->result[] = new ResultRow($row);
      }
    }
    catch (Exception $e) {
      \Drupal::logger('govuk_notify_views_backend')->notice("Failed to submit message to GovUK Notify: " . $e->getMessage());
      return FALSE;
    }
    catch (NotifyException $e) {
      \Drupal::logger('govuk_notify_views_backend')->notice("Failed to submit message to GovUK Notify: " . $e->getMessage());
      return FALSE;
    }
  }

  /**
   *
   */
  public function ensureTable($table, $relationship = NULL) {
    return '';
  }

  /**
   *
   */
  public function addField($table, $field, $alias = '', $params = []) {
    return $field;
  }

}
