<?php
// $Id$

/**
 * @file rules integration for the comment module
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implementation of hook_rules_event_info().
 */
function rules_comment_event_info() {
  return array(
    'comment_insert' => array(
      'label' => t('After saving a new comment'),
      'group' => t('comment'),
      'variables' => array(
        'comment' => array('type' => 'comment', 'label' => t('created comment')),
      ),
      'access callback' => 'rules_comment_integration_access',
    ),
    'comment_update' => array(
      'label' => t('After updating an existing comment'),
      'group' => t('comment'),
      'variables' => array(
        'comment' => array('type' => 'comment', 'label' => t('updated comment')),
        'comment_unchanged' => array('type' => 'comment', 'label' => t('unchanged comment'), 'handler' => 'rules_events_comment_var_unchanged'),
      ),
      'access callback' => 'rules_comment_integration_access',
    ),
    'comment_presave' => array(
      'label' => t('Before saving a comment'),
      'group' => t('comment'),
      'variables' => array(
        'comment' => array('type' => 'comment', 'label' => t('updated comment')),
        'comment_unchanged' => array('type' => 'comment', 'label' => t('unchanged comment'), 'handler' => 'rules_events_comment_var_unchanged'),
      ),
      'access callback' => 'rules_comment_integration_access',
    ),
    'comment_view' => array(
      'label' => t('A comment is viewed'),
      'group' => t('comment'),
      'variables' => array(
        'comment' => array('type' => 'comment', 'label' => t('viewed comment')),
      ),
      'access callback' => 'rules_comment_integration_access',
      'help' => t("Note that if drupal's page cache is enabled, this event won't be generated for pages served from cache."),
    ),
    'comment_delete' => array(
      'label' => t('After a comment has been cancelled'),
      'group' => t('comment'),
      'variables' => array(
        'comment' => array('type' => 'comment', 'label' => t('deleted comment')),
        'method' => array(
          'type' => 'text',
          'label' => t('cancellation method'),
          'options list' => 'rules_comment_cancel_methods',
        ),
      ),
      'access callback' => 'rules_comment_integration_access',
    ),
    'comment_publish' => array(
      'label' => t('A comment has been published'),
      'group' => t('comment'),
      'variables' => array(
        'comment' => array('type' => 'comment', 'label' => t('published comment')),
      ),
      'access callback' => 'rules_comment_integration_access',
    ),
    'comment_unpublish' => array(
      'label' => t('A comment has been unpublished'),
      'group' => t('comment'),
      'variables' => array(
        'comment' => array('type' => 'comment', 'label' => t('unpublished comment')),
      ),
      'access callback' => 'rules_comment_integration_access',
    ),
  );
}

/**
 * Comment integration access callback.
 */
function rules_comment_integration_access($type, $name) {
  if ($type == 'event' || $type == 'condition') {
    return entity_metadata_entity_access('view', 'comment');
  }
}

/**
 * @}
 */
