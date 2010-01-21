<?php
// $Id$

/**
 * @file rules integration for the user module
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implementation of hook_rules_event_info().
 */
function rules_user_event_info() {
  return array(
    'user_insert' => array(
      'label' => t('After saving a new user account'),
      'group' => t('User'),
      'variables' => array(
        'account' => array('type' => 'user', 'label' => t('registered user')),
      ),
      'access callback' => 'rules_user_integration_access',
    ),
    'user_update' => array(
      'label' => t('After updating an existing user account'),
      'group' => t('User'),
      'variables' => array(
        'account' => array('type' => 'user', 'label' => t('updated user')),
        'account_unchanged' => array('type' => 'user', 'label' => t('unchanged user'), 'handler' => 'rules_events_account_var_unchanged'),
      ),
      'access callback' => 'rules_user_integration_access',
    ),
    'user_presave' => array(
      'label' => t('Before saving a user account'),
      'group' => t('User'),
      'variables' => array(
        'account' => array('type' => 'user', 'label' => t('updated user')),
        'account_unchanged' => array('type' => 'user', 'label' => t('unchanged user'), 'handler' => 'rules_events_account_var_unchanged'),
      ),
      'access callback' => 'rules_user_integration_access',
    ),
    'user_view' => array(
      'label' => t('User account page is viewed'),
      'group' => t('User'),
      'variables' => array(
        'account' => array('type' => 'user', 'label' => t('viewed user')),
        'view_mode' => array(
          'type' => 'text',
          'label' => t('view mode'),
          'options list' => 'rules_get_entity_view_modes',
        ),
      ),
      'access callback' => 'rules_user_integration_access',
      'help' => t("Note that if drupal's page cache is enabled, this event won't be generated for pages served from cache."),
    ),
    'user_delete' => array(
      'label' => t('After a user account has been cancelled'),
      'group' => t('User'),
      'variables' => array(
        'account' => array('type' => 'user', 'label' => t('deleted user')),
        'method' => array(
          'type' => 'text',
          'label' => t('cancellation method'),
          'options list' => 'rules_user_cancel_methods',
        ),
      ),
      'access callback' => 'rules_user_integration_access',
    ),
    'user_login' => array(
      'label' => t('User has logged in'),
      'group' => t('User'),
      'variables' => array(
        'account' => array('type' => 'user', 'label' => t('logged in user')),
      ),
      'access callback' => 'rules_user_integration_access',
    ),
    'user_logout' => array(
      'label' => t('User has logged out'),
      'group' => t('User'),
      'variables' => array(
        'account' => array('type' => 'user', 'label' => t('logged out user')),
      ),
      'access callback' => 'rules_user_integration_access',
    ),
  );
}

/**
 * Options list for user cancel methods.
 */
function rules_user_cancel_methods() {
  module_load_include('inc', 'user', 'user.pages');
  foreach (user_cancel_methods() as $method => $form) {
    $methods[$method] = $form['#title'];
  }
  return $methods;
}

/**
 * User integration access callback.
 */
function rules_user_integration_access($type, $name) {
  if ($type == 'event' || $type == 'condition') {
    return entity_metadata_entity_access('view', 'user');
  }
}

/**
 * @}
 */
