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
  // Add actions for all rule sets.
  foreach (rules_get_configured_items('rule_sets') as $name => $set_info) {
    $items[ 'rules_action_invoke_set_'. $name ] = $set_info + array(
      'module' => 'Rule Sets',
      'base' => 'rules_action_invoke_set',
      'set' => $name,
    );
  }
  // Add drupal 6 core actions.
  $core_actions = actions_list();
  foreach ($core_actions as $name => $core_action_info) {
    $type_map = rules_gather_data('rules_action_type_map');
    $items[ 'rules_core_'. $name ] = rules_core_action_transform_info($name, $core_action_info, $type_map);
  }
  array_filter($items);
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

/**
 * Transforms action info written for core to the rules format.
 * If the action should not be used by the rules modules, an empty array is returned.
 */
function rules_core_action_transform_info($name, $core_action_info, $type_map) {

  if (isset($core_action_info['rules_ignore']) && $core_action_info['rules_ignore']) {
    return array();
  }
  $info = $type_map[$core_action_info['type']];
  // If there is no entry in the type map, so we don't use this action.
  if (!isset($info)) {
    return array();
  }
  $info['label'] = $core_action_info['description'];
  $info['base'] = 'rules_core_action_execute';
  $info['action_name'] = $name;
  $info['configurable'] = $core_action_info['configurable'];
  return $info;
}

/**
 * Action implementation: Execute a core action
 */
function rules_core_action_execute() {
  $args = func_get_args();
  $state = array_pop($args);
  $element = array_pop($args);
  $info = rules_get_element_info($element);

  call_user_func_array($info['action_name'], $args);
}

/**
 * Action form implementation: Get the core actions form
 */
function rules_core_action_execute_form($settings, &$form, &$form_state) {
  $element = $form_state['element'];
  $info = rules_get_element_info($element);
  if ($info['configurable']) {
    $function = $info['action_name']. '_form';
    $form['settings'] = $function($settings);
  }
}

/**
 * Action form submit implementation: Apply the core actions form submit
 */
function rules_core_action_execute_submit(&$settings, &$form, &$form_state) {
  $element = $form_state['element'];
  $info = rules_get_element_info($element);
  if ($info['configurable']) {
    $function = $info['action_name']. '_submit';
    $settings = $function($form['settings'], $form_state) + $settings;
  }
}


/**
 * Implementation of hook_rules_action_type_map().
 *
 * Maps core action types to rules action info.
 */
function rules_rules_action_type_map() {
  return array(
    'node' => array(
      'module' => 'Node',
      'arguments' => array(
        'node' => array(
          'label' => t('Content'),
          'type' => 'node',
        ),
      ),
    ),
    'comment' => array(
      'module' => 'Comment',
      'arguments' => array(
        'comment' => array(
          'label' => t('Comment'),
          'type' => 'comment',
         ),
      ),
    ),
    'user' => array(
      'module' => 'User',
      'arguments' => array(
        'user' => array(
          'label' => t('User'),
          'type' => 'user',
        ),
      ),
    ),
    'system' => array(
      'module' => 'System',
    ),
  );
}


/**
 * Implementation of hook_rules_action_info_alter().
 *
 * Adapts the action info of some core actions to better fit for being
 * used with rules.
 */
function rules_rules_action_info_alter(&$actions) {
  // This action doesn't take the user argument.
  $actions['rules_core_user_block_ip_action']['arguments'] = array();
  // This action can be configured to other users than the current one, so improve the label
  $actions['rules_core_user_block_user_action']['label'] = t('Block a user');
}


