<?php
// $Id$
/**
 * @file rules integration for the rules module
 */

/**
 * Implementation of hook_rules_data_type_info()
 */
function rules_rules_data_type_info() {
  return array(
    'string' => array(
      'label' => t('String'),
      'class' => 'rules_data_type_string',
    ),
  );
}

/**
 * Rules string data type
 * @ingroup rules
 */
class rules_data_type_string extends rules_data_type {

  function get_info() {
    return array(
      'saveable' => FALSE,
      'identifiable' => FALSE,
      'eval_input' => TRUE,
    );
  }

  /**
   * Gets the input form for the data
   * Implement it, if your data is not identifiable.
   */
  function get_default_input_form($info, $value) {
    $info += array('long' => FALSE, 'required' => TRUE, 'description' => '');
    return array(
      '#type' => $info['long'] ? 'textfield' : 'textarea',
      '#title' => $info['label'],
      '#description' => $info['description'],
      '#required' => $info['required'],
      '#default_value' => $value,
    );
  }
}

/**
 * Implementation of hook_rules_action_info
 */
function rules_rules_action_info() {
  $items = array();
  foreach (rules_get_configured_items('rule_sets') as $name => $set_info) {
    $items[ 'rules_action_invoke_set_'. $name ] = $set_info + array(
      'module' => 'Rule Sets',
      'base' => 'rules_action_invoke_set',
      'set' => $name,
    );
  }
  return $items;
}

/**
 * Base action implementation for invoking all rule sets
 */
function rules_action_invoke_set() {
  $args = func_get_args();
  $state = array_pop($args);
  $element = array_pop($args);
  $settings = array_pop($args);

  array_unshift($args, $element['#info']['set']);
  call_user_func_array('rules_invoke_rule_set', $args);
}
