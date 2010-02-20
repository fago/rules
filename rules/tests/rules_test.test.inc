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
function rules_action_test_reference($data) {
  $data['changed'] = TRUE;
  return array('arg' => $data);
}

/**
 * Condition: Check for selected content types
 */
function rules_condition_content_is_type($node, $type) {
  return in_array($node->type, $type);
}

/**
 * Condition: Check if the node is published
 */
function rules_condition_content_is_published($node, $settings) {
  return $node->status == 1;
}

/**
 * Loads a node
 */
function rules_action_load_node($nid, $vid = NULL) {
  return array('node_loaded' => node_load($nid, $vid ? $vid : NULL));
}

/**
 * Action "Delete a node".
 */
function rules_action_delete_node($node) {
  node_delete($node->nid);
}

/**
 * Action "Create a node"
 */
function rules_action_node_create($author, $title, $settings) {
  if (!$settings['node_access'] || node_access('create', $settings['type'], $author)) {
    module_load_include('inc', 'node', 'node.pages');

    $node = (object)array('type' => $settings['type']);
    node_object_prepare($node);

    $node->name  = $author->name;
    $node->uid   = $author->uid;
    $node->type  = $settings['type'];
    $node->title = $title;
    return array('node_added' => $node);
  }
}