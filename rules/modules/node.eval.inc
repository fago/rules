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
