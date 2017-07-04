<?php

/**
 * @file
 * Views backend for GovUK Notify.
 */

/**
 * Implements hook_views_data().
 */
function govuk_notify_views_backend_views_data() {
  $data = [];
  // Base data.
  $data['govuk_notify_views_backend']['table']['group'] = t('GovUK Notification Message Log');
  $data['govuk_notify_views_backend']['table']['base'] = [
    'title' => t('GovUK Notification Message Log'),
    'help' => t('GovUK Notification Message Log'),
    'query_id' => 'govuk_notify_views_backend',
  ];

  $fields = [
    'id',
    'type',
    'created_at',
    'updated_at',
    'sent_at',
    'status',
    'created_by',
    'type',
    'body',
    'subject',
  ];

  foreach ($fields as $field) {
    $data['govuk_notify_views_backend'][$field] = [
      'title' => t('GovUK Notification - @field', ['@field' => $field]),
      'help' => t('GovUK Notification - @field', ['@field' => $field]),
      'field' => [
        'id' => 'standard',
      ],
    ];
  }

  return $data;
}
