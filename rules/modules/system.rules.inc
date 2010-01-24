<?php
// $Id$

/**
 * @file rules integration for the system module
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implements hook_rules_file_info() on behalf of the system module.
 */
function rules_system_file_info() {
  return array('modules/system.eval');
}

/**
 * Implements hook_rules_event_info() on behalf of the system module.
 */
function rules_system_event_info() {
  return array(
    'init' => array(
      'label' => t('Drupal is initializing'),
      'group' => t('System'),
      'help' => t("Be aware that some actions might initialize the theme system. After that, it's impossible for any module to change the used theme."),
      'access callback' => 'rules_system_integration_access',
    ),
    'cron' => array(
      'label' => t('Cron maintenance tasks are performed'),
      'group' => t('System'),
      'access callback' => 'rules_system_integration_access',
    ),
    'watchdog' => array(
      'label' => t('System log entry is created'),
      'variables' => array(
        'log_entry' => array(
          'type' => 'log_entry',
          'label' => t('Log entry'),
          'data info' => _rules_system_watchdog_log_entry_info(),
        ),
      ),
      'group' => t('System'),
      'access callback' => 'rules_system_integration_access',
    ),
  );
}

/**
 * Defines entity info for watchdog log entries, used by the log entry data
 * type to provide an useful metadata wrapper.
 */
function _rules_system_watchdog_log_entry_info() {
  $info['properties'] = array(
    'type' => array(
      'type' => 'text',
      'label' => t('The category to which this message belongs'),
    ),
    'message' => array(
      'type' => 'text',
      'label' => ('Log message'),
      'getter callback' => 'rules_system_log_get_message',
      'sanitized' => TRUE,
    ),
    'severity' => array(
      'type' => 'integer',
      'label' => t('Severity'),
      'options list' => 'watchdog_severity_levels',
    ),
    'request_uri' => array(
      'type' => 'uri',
      'label' => t('Request uri'),
    ),
    'link' => array(
      'type' => 'text',
      'label' => t('An associated, HTML formatted link'),
    ),
  );
  return $info;
}


/**
 * Implements hook_rules_action_info() on behalf of the system module.
 */
function rules_system_action_info() {
  return array(
    'drupal_message' => array(
      'label' => t('Show a configurable message on the site'),
      'group' => t('System'),
      'parameter' => array(
        'message' => array(
          'type' => 'text',
          'label' => t('Message'),
          'sanitized' => TRUE,
        ),
        'error' => array(
          'type' => 'boolean',
          'label' => t('Show as error'),
          'optional' => TRUE,
          'default value' => FALSE,
        ),
      ),
      'callbacks' => array('execute' => 'rules_action_drupal_message'),
      'access callback' => 'rules_system_integration_access',
    ),
    'redirect' => array(
      'label' => t('Page redirect'),
      'group' => t('System'),
      'parameter' => array(
        'url' => array('type' => 'uri', 'label' => t('URL')),
      ),
      'callbacks' => array('execute' => 'rules_action_drupal_goto'),
      'help' => t('Enter a Drupal path, path alias, or external URL to redirect to. Enter (optional) queries after "?" and (optional) anchor after "#".'),
      'access callback' => 'rules_system_integration_access',
    ),
  );
}

/**
 * System integration access callback.
 */
function rules_system_integration_access($type, $name) {
  return user_access('administer site configuration');
}

/**
 * @}
 */
