<?php
// $Id$

/**
 * @file Include file for testing file inclusion.
 */

/**
 * Extender for the node data type.
 */
function rules_test_custom_node_save($object) {
  throw new RulesException('Custom save method invoked.');
}


/**
 * Custom help callback for the rules_node_publish_action
 */
function rules_test_custom_help() {
  return 'custom';
}

/**
 * Action callback
 */
function rules_action_test_reference(&$data) {
  $data['changed'] = TRUE;
}
