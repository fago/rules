<?php
// $Id$

/**
 * @file rules integration for the system module
 */

/**
 * Implementation of hook_rules_action_info().
 */
function system_rules_action_info() {
  return array(
    'rules_action_drupal_message' => array(
      'label' => t('Show a configurable message on the site'),
      'module' => 'System',
      'eval input' => array('message'),
    ),
  );
}

function rules_action_drupal_message($settings) {
  drupal_set_message($settings['message'], $settings['error'] ? 'error' : 'status');
}
