<?php
// $Id$

/**
 * @file
 * Contains rules integration for the node module needed during rule evaluation.
 *
 * @addtogroup rules
 * @{
 */

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
 * Condition: Check if the node is sticky
 */
function rules_condition_content_is_sticky($node, $settings) {
  return $node->sticky == 1;
}

/**
 * Condition: Check if the node is promoted to the frontpage
 */
function rules_condition_content_is_promoted($node, $settings) {
  return $node->promote == 1;
}

/**
 * Condition: Check if the node is new
 */
function rules_condition_content_is_new($node, $settings = array()) {
  return empty($node->nid) || !empty($node->is_new);
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
 * @}
 */
