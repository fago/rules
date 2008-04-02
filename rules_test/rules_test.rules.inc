<?php
// $Id$
/**
 * @file Define some conditions and actions, used by the tests
 */

/**
 * Implementation of hook_rules_condition_info
 */
function rules_test_rules_condition_info() {
  return array(
    'rules_condition_alter_arg' => array(
      'label' => 'Alter entity',
      'arguments' => array('rules_test' => array('entity' => 'rules_test', 'label' => 'test')),
     ),
    'rules_condition_check_for_altered_arg' => array(
      'label' => 'Check for altered entity',
      'arguments' => array('rules_test' => array('entity' => 'rules_test', 'label' => 'test')),
     ),
  );
}

/**
 * Implementation of hook_rules_action_info
 */
function rules_test_rules_action_info() {
  return array(
    'rules_log_msg' => array(
      'label' => 'Logs a message to the rules log',
      'arguments' => array(),
     ),
    'rules_change_arg' => array(
      'label' => 'Changes the argument',
      'arguments' => array('rules_test' => array('entity' => 'rules_test', 'label' => 'test')),
     ),
  );
}

function rules_condition_alter_arg(&$array) {
  $array['altered'] = TRUE;
  return TRUE;
}

function rules_condition_check_for_altered_arg($array) {
  return isset($array['altered']) && $array['altered'];
}

function rules_log_msg($settings) {
  rules_log($settings['msg']);
}

function rules_change_arg($array) {
  $array['altered'] = TRUE;
  return array('rules_test' => $array);
}
