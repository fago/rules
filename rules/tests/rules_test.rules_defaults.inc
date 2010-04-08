<?php
// $Id$

/**
 * @file Includes any rules integration provided by the module.
 */


/**
 * Implements hook_default_rules_configuration().
 */
function rules_test_default_rules_configuration() {
  $rule = rules_reaction_rule();
  $rule->label = 'example default rule';
  $rule->active = FALSE;
  $rule->event('node_update')
       ->condition(rules_condition('data_is', array('data:select' => 'node:status', 'value' => TRUE))->negate())
       ->condition('data_is', array('data:select' => 'node:type', 'value' => 'page'))
       ->action('drupal_message', array('message' => 'A node has been updated.'));

  $configs['rules_test_default_1'] = $rule;
  return $configs;
}
