<?php

/**
 * @file
 * Contains rules integration for the user module needed during evaluation.
 *
 * @addtogroup rules
 * @{
 */

/**
 * Condition user: condition to check whether user has particular roles
 */
function rules_condition_user_has_role($account, $roles, $operation = 'AND') {
  switch ($operation) {
    case 'OR':
      foreach ($roles as $rid) {
        if (isset($account->roles[$rid])) {
          return TRUE;
        }
      }
      return FALSE;

    case 'AND':
      foreach ($roles as $rid) {
        if (!isset($account->roles[$rid])) {
          return FALSE;
        }
      }
      return TRUE;
  }
}

/**
 * Condition: User is blocked.
 */
function rules_condition_user_is_blocked($account) {
  return $account->status == 0;
}

/**
 * Action: Adds roles to a particular user.
 */
function rules_action_user_add_role($account, $roles) {
  if ($account->uid) {
    // Get role list (minus the anonymous)
    $role_list = user_roles(TRUE);

    foreach ($roles as $rid) {
      $account->roles[$rid] = $role_list[$rid];
    }
  }
  else {
    return FALSE;
  }
}

/**
 * Action: Remove roles from a given user.
 */
function rules_action_user_remove_role($account, $roles) {
  if ($account->uid) {
    foreach ($roles as $rid) {
      // If the user has this role, remove it.
      if (isset($account->roles[$rid])) {
        unset($account->roles[$rid]);
      }
    }
  }
  else {
    return FALSE;
  }
}

/**
 * Action: Block a user.
 */
function rules_action_user_block($account) {
  $account->status = 0;
  drupal_session_destroy_uid($account->uid);
}

/**
 * Action: Unblock a user.
 */
function rules_action_user_unblock($account) {
  $account->status = 1;
}

/**
 * @}
 */
