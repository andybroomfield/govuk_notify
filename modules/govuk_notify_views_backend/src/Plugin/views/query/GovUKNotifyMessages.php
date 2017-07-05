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
  public function addWhere($group, $field, $value = NULL, $operator = NULL) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }
    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->setWhereGroup('AND', $group);
    }
    $this->where[$group]['conditions'][] = [
      'field' => $field,
      'value' => $value,
      'operator' => $operator,
    ];
  }

  /**
   * Execute the view by calling the Gov.UK Notify API.
   *
   * This builds up the filters then calls the Gov.UK Notify API then adds each
   * returned record to the view's result.
   *
   * @todo - paging
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {

    $index = 0;

    // I think this is needed in order to recover safely from an aborted query.
    $view->initPager();

    try {

      // Build the filters we're going to apply.
      $filters = [];
      if (!empty($this->where)) {
        foreach ($this->where as $where_group => $where) {
          foreach ($where['conditions'] as $condition) {
            $filter_name = ltrim($condition['field'], '.');
            if ($filter_name == 'type') {
              $filter_name = 'template_type';
            }
            $filters[$filter_name] = current($condition['value']);
          }
        }
      }

      // Call the API.
      $response = $this->notifyClient->listNotifications($filters);

      // Add each record to the results.
      foreach ($response['notifications'] as $notification) {
        foreach ($notification as $notification_key => $notification_value) {
          $row[$notification_key] = $notification_value;
        }
        $row['index'] = $index++;
        $view->result[] = new ResultRow($row);
      }
    }
    catch (NotifyException $e) {
      // @todo - better message here.
      \Drupal::logger('govuk_notify_views_backend')->notice("Exception occurred.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function ensureTable($table, $relationship = NULL) {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function addField($table, $field, $alias = '', $params = []) {
    return $field;
  }

}
