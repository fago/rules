<?php
// $Id$

/**
 * @file Invokes events on behalf core modules. Usually this should be
 *   directly in the module providing rules integration instead.
 *
 * @addtogroup rules
 * @{
 */

/**
 * Node events
 * For getting the unchanged node we currently have to implement a workaround.
 * If http://drupal.org/node/651240 gets in, we can simplify that.
 */

function rules_node_view($node, $view_mode) {
  rules_invoke_event('node_view', $node, $view_mode);
}

function rules_node_presave($node) {
  $nodes = &drupal_static('rules_node_unchanged', array());
  if (!empty($node->nid)) {
    // Save the unchanged object now so that we can make use of it later on.
    // See rules_events_node_var_unchanged().
    array_push($nodes, clone node_load($node->nid));
  }
  rules_invoke_event('node_presave', $node);
}

function rules_node_update($node) {
  rules_invoke_event('node_update', $node);
  // Pop the unchanged node so recursive invocations work right.
  $nodes = &drupal_static('rules_node_unchanged', array());
  array_pop($nodes);
}

function rules_node_insert($node) {
  rules_invoke_event('node_insert', $node);
}

function rules_node_delete($node) {
  rules_invoke_event('node_delete', $node);
}

/**
 * Gets the node object, that doesn't contain the changes.
 *
 * @see rules_events_node_variables()
 */
function rules_events_node_var_unchanged($node) {
  if (empty($node->nid)) {
    throw new RulesException("Can't load the unchanged node variable as there is none yet.");
  }
  // Use read-only so actually don't change the array.
  $nodes = drupal_static('rules_node_unchanged', array());
  return array_pop($nodes);
}

/**
 * Invoke user events.
 */

function rules_user_view($account, $view_mode) {
  rules_invoke_event('user_view', $account, $view_mode);
}

function rules_user_presave(&$edit, $account, $category) {
  if ($category == 'account') {
    $accounts = &drupal_static('rules_user_unchanged', array());
    if (!empty($account->uid)) {
      // Save the unchanged object now so that we can make use of it later on.
      // See rules_events_node_var_unchanged().
      array_push($accounts, clone user_load($account->uid));
    }
    rules_invoke_event('user_presave', $account);
  }
}

function rules_user_insert(&$edit, $account, $category) {
  if ($category == 'account') {
    rules_invoke_event('user_insert', $account);
  }
}

function rules_user_update(&$edit, $account, $category) {
  if ($category == 'account') {
    rules_invoke_event('user_update', $account);
    // Pop the unchanged user so recursive invocations work right.
    $accounts = &drupal_static('rules_user_unchanged', array());
    array_pop($accounts);
  }
}

function rules_user_cancel(&$edit, $account, $method) {
  rules_invoke_event('user_delete', $account, $method);
}

function rules_user_login(&$edit, $account) {
  rules_invoke_event('user_login', $account);
}

function rules_user_logout(&$edit, $account) {
  rules_invoke_event('user_logout', $account);
}

/**
 * Gets the account object, that doesn't contain the changes.
 */
function rules_events_account_var_unchanged($account) {
  if (empty($account->uid)) {
    throw new RulesException("Can't load the unchanged account variable as there is none yet.");
  }
  // Use read-only so actually don't change the array.
  $accounts = drupal_static('rules_node_unchanged', array());
  return array_pop($accounts);
}


/**
 * Comment events.
 */

function rules_comment_view($comment) {
  rules_invoke_event('comment_view', $comment);
}

function rules_comment_presave($comment) {
  $comments = &drupal_static('rules_comment_unchanged', array());
  if (!empty($comment->cid)) {
    // Save the unchanged object now so that we can make use of it later on.
    // See rules_events_comment_var_unchanged().
    array_push($comments, clone comment_load($comment->cid));
  }
  rules_invoke_event('comment_presave', $comment);
}

function rules_comment_update($comment) {
  rules_invoke_event('comment_update', $comment);
  // Pop the unchanged comment so recursive invocations work right.
  $comments = &drupal_static('rules_comment_unchanged', array());
  array_pop($comments);
}

function rules_comment_insert($comment) {
  rules_invoke_event('comment_insert', $comment);
}

function rules_comment_delete($comment) {
  rules_invoke_event('comment_delete', $comment);
}

/**
 * Gets the comment object, that doesn't contain the changes.
 */
function rules_events_comment_var_unchanged($comment) {
  if (empty($comment->cid)) {
    throw new RulesException("Can't load the unchanged comment variable as there is none yet.");
  }
  // Use read-only so actually don't change the array.
  $comments = drupal_static('rules_comment_unchanged', array());
  return array_pop($comments);
}


/**
 * Taxonomy events.
 */

function rules_taxonomy_term_update($taxonomy_term) {
  rules_invoke_event('taxonomy_term_update', $taxonomy_term);
}

function rules_taxonomy_term_insert($taxonomy_term) {
  rules_invoke_event('taxonomy_term_insert', $taxonomy_term);
}

function rules_taxonomy_term_delete($taxonomy_term) {
  rules_invoke_event('taxonomy_term_delete', $taxonomy_term);
}

function rules_taxonomy_vocabulary_update($taxonomy_vocabulary) {
  rules_invoke_event('taxonomy_vocabulary_update', $taxonomy_vocabulary);
}

function rules_taxonomy_vocabulary_insert($taxonomy_vocabulary) {
  rules_invoke_event('taxonomy_vocabulary_insert', $taxonomy_vocabulary);
}

function rules_taxonomy_vocabulary_delete($taxonomy_vocabulary) {
  rules_invoke_event('taxonomy_vocabulary_delete', $taxonomy_vocabulary);
}

/**
 * System events. Note that rules_init() is the main module file is used to
 * invoke the init event.
 */

function rules_cron() {
  rules_invoke_event('cron');
}

function rules_watchdog($log_entry) {
  rules_invoke_event('watchdog', $log_entry);
}

/**
 * Data actions access callback.
 * Returns TRUE if at least one type is available for configuring the action.
 */
function rules_data_action_access($type, $name) {
  if ($name == 'data_fetch' || $name == 'data_create') {
    $types = array_keys(rules_data_action_type_options($type, $name));
    $op = $name == 'data_create' ? 'create' : 'view';
  }
  elseif ($name == 'data_save' || $name == 'data_delete') {
    $cache = rules_get_cache();
    $types = $cache['action_info'][$name]['parameter']['data']['type'];
    $op = $name == 'data_save' ? 'save' : 'delete';
  }
  foreach ($types as $key => $type) {
    $data = rules_wrap_data($data = NULL, array('type' => $type));
    if ($data->facesAs('RulesDataAccessInterface') && !$data->access($op)) {
      unset($types[$key]);
    }
  }
  return !empty($types);
}

/**
 * Options list callback for data actions.
 *
 * @param $type
 *   The element type, e.g. 'action' or 'condition.
 * @param $name
 *   The name of the element to return options for.
 */
function rules_data_action_type_options($type, $name) {
  $cache = rules_get_cache();
  $interface = $name == 'data_fetch' ? 'RulesDataIdentifiableInterface' : 'RulesDataCreatableInterface';
  // Get an array of types that are savable.
  foreach ($cache['data_info'] as $type => $info) {
    if (RulesExtendable::itemFacesAs($info, $interface)) {
      $types[$type] = $info['label'];
    }
  }
  return $types;
}

/**
 * Gets all view modes of an entity for an entity_view event.
 */
function rules_get_entity_view_modes($type, $name) {
  if ($type == 'event') {
    $entity_type = substr($name, 0, -5);
    $info = entity_get_info($entity_type);
    foreach ($info['view modes'] as $mode => $mode_info) {
      $modes[$mode] = $mode_info['label'];
    }
    return $modes;
  }
}


/**
 * @}
 */