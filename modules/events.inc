<?php

/**
 * @file Invokes events on behalf core modules. Usually this should be
 *   directly in the module providing rules integration instead.
 *
 * @addtogroup rules
 * @{
 */


/**
 * Gets an unchanged entity that doesn't contain any recent changes. This
 * handler assumes the name of the variable for the changed entity is the same
 * as for the unchanged entity but without the trailing "_unchanged"; e.g., for
 * the "node_unchanged" variable the handler assumes there is a "node" variable.
 */
function rules_events_entity_unchanged($arguments, $name, $info) {
  // Cut of the trailing _unchanged.
  $var_name = substr($name, 0, -10);
  $entity = $arguments[$var_name];
  if (isset($entity->original)) {
    return $entity->original;
  }
}

/**
 * Node events
 * For getting the unchanged node we currently have to implement a workaround.
 * If http://drupal.org/node/651240 gets in, we can simplify that.
 */

function rules_node_view($node, $view_mode) {
  rules_invoke_event('node_view', $node, $view_mode);
}

function rules_node_presave($node) {
  rules_invoke_event('node_presave', $node);
}

function rules_node_update($node) {
  rules_invoke_event('node_update', $node);
}

function rules_node_insert($node) {
  rules_invoke_event('node_insert', $node);
}

function rules_node_delete($node) {
  rules_invoke_event('node_delete', $node);
}



/**
 * Invoke user events.
 */

function rules_user_view($account, $view_mode) {
  rules_invoke_event('user_view', $account, $view_mode);
}

/**
 * We don't want to use hook_user_presave() here as it would requires us
 * to mess with the $edit array to get an updated $account.
 */
function rules_entity_presave($entity, $entity_type) {
  if ($entity_type == 'user') {
    rules_invoke_event('user_presave', $entity);
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
  }
}

function rules_user_delete($account) {
  rules_invoke_event('user_delete', $account);
}

function rules_user_login(&$edit, $account) {
  rules_invoke_event('user_login', $account);
}

function rules_user_logout($account) {
  rules_invoke_event('user_logout', $account);
}

/**
 * Comment events.
 */

function rules_comment_view($comment) {
  rules_invoke_event('comment_view', $comment);
}

function rules_comment_presave($comment) {
  rules_invoke_event('comment_presave', $comment);
}

function rules_comment_update($comment) {
  rules_invoke_event('comment_update', $comment);
}

function rules_comment_insert($comment) {
  rules_invoke_event('comment_insert', $comment);
}

function rules_comment_delete($comment) {
  rules_invoke_event('comment_delete', $comment);
}


/**
 * Taxonomy events.
 */

function rules_taxonomy_term_update($taxonomy_term) {
  rules_invoke_event('taxonomy_term_update', $taxonomy_term);
}

function rules_taxonomy_term_presave($taxonomy_term) {
  rules_invoke_event('taxonomy_term_presave', $taxonomy_term);
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

function rules_taxonomy_vocabulary_presave($taxonomy_vocabulary) {
  rules_invoke_event('taxonomy_vocabulary_presave', $taxonomy_vocabulary);
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
 * Getter callback for the log entry message property.
 */
function rules_system_log_get_message($log_entry) {
  return t($log_entry['message'], (array)$log_entry['variables']);
}

/**
 * @}
 */