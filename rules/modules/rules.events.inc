<?php
// $Id$
/**
 * @file rules Invokes events for supported modules. Usually this should be directly
 *   in the module providing rules integration instead.
 */

/**
 * Implementation of hook_nodeapi().
 */
function rules_nodeapi(&$node, $op, $teaser = NULL, $page = NULL) {

  if (in_array($op, array('view', 'insert', 'update', 'presave', 'delete'))) {
    // We pass the node by reference so that changes are saved automatically on op submit
    $arguments = ($op == 'view') ? array('node' => &$node, 'teaser' => $teaser, 'page' => $page) : array('node' => &$node);
    rules_invoke_event('node_'. $op, $arguments);
  }
}

/**
 * Implementation of hook_user().
 */
function rules_user($op, &$edit, &$account, $category = NULL) {
  static $account_unchanged;

  // We don't support updates for other categories than 'account'
  if ($op == 'update' && $category == 'account') {
    // Save the unchanged account for the use with op after_update.
    $account_unchanged = drupal_clone($account);
  }
  else if ($op == 'after_update' && $category == 'account') {
    rules_invoke_event('user_update', array('account' => &$account, 'account_unchanged' => $account_unchanged));
  }
  else if (in_array($op, array('insert', 'login', 'logout', 'view', 'delete'))) {
    rules_invoke_event('user_'. $op, array('account' => &$account));

    // Allow adding user roles during registration
    if ($op == 'insert' && isset($account->roles)) {
      $edit += array('roles' => array());
      $edit['roles'] += $account->roles;
    }
  }
}

/**
 * Implementation of hook_init().
 */
function rules_init() {
  rules_invoke_event('init');
}

/**
 * Implementation of hook_cron().
 */
function rules_cron() {
  rules_invoke_event('cron');
}

/**
 * Implementation of hook_comment().
 */
function rules_comment($comment, $op) {
  if (in_array($op, array('insert', 'update', 'delete', 'view', 'publish', 'unpublish'))) {
    if (is_array($comment)) {
      $comment = (object)$comment;
    }
    rules_invoke_event('comment_'. $op, $comment);
  }
}

/**
 * Implementation of hook_taxonomy().
 */
function rules_taxonomy($op, $type, $array) {
  if ($type == 'term' && in_array($op, array('insert', 'update'))) {
    $taxonomy_object = (object) $array;
    rules_invoke_event('taxonomy_term_'. $op, $taxonomy_object);
  }
}