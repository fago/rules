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
      'label' => t('User is going to view a page'),
      'group' => t('System'),
      'provides' => rules_events_global_user_parameter(),
      'help' => t("Be aware that some actions might initialize the theme system. After that, it's impossible for any module to change the used theme."),
    ),
    'cron' => array(
      'label' => t('Cron maintenance tasks are performed'),
      'group' => t('System'),
      'provides' => rules_events_global_user_parameter(),
    ),
  );
}

/**
 * Implements hook_rules_action_info() on behalf of the system module.
 */
function rules_system_action_info() {
  return array(
    'rules_action_drupal_message' => array(
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
    ),
    'rules_action_drupal_goto' => array(
      'label' => t('Page redirect'),
      'group' => t('System'),
      'parameter' => array(
        'url' => array('type' => 'uri', 'label' => t('URL')),
      ),
      'help' => t('Enter a Drupal path, path alias, or external URL to redirect to. Enter (optional) queries after "?" and (optional) anchor after "#".'),
    ),
  );
}

/**
 * @}
 */
