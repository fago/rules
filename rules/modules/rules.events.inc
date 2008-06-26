<?php
// $Id$
/**
 * @file rules Invokes events for supported modules 
 */

/**
 * Implementation of hook_nodeapi
 * @ingroup rules
 */
function rules_nodeapi(&$node, $op, $teaser = NULL, $page = NULL) {

  if (in_array($op, array('view', 'insert', 'update', 'submit', 'delete'))) {
    //we pass the node by reference so that changes are saved automatically on op submit
    $arguments = ($op == 'view') ? array('node' => &$node, 'teaser' => $teaser, 'page' => $page) : array('node' => &$node);
    rules_invoke_event('node_'. $op, $arguments);
  }
}

/**
 * Implementation of hook_user
 * @ingroup rules
 */
function rules_user($op, $edit, &$account, $category = NULL) {
  static $account_unchanged;

  //we don't support updates for other categories than 'account'
  if ($op == 'update' && $category == 'account') {
    //save the unchanged account for the use with op after_update
    $account_unchanged = $account;
  }
  else if ($op == 'after_update' && $category == 'account') {
    rules_invoke_event('user_update', array('account' => &$account, 'account_unchanged' => $account_unchanged));
  }
  else if (in_array($op, array('insert', 'login', 'logout', 'view', 'delete'))) {
    rules_invoke_event('user_'. $op, array('account' => &$account));
  }
}
