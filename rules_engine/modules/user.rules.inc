<?php
// $Id$
/**
 * @file rules integration for the user module 
 */

/** 
 * Implementation of hook_rules_event_info()
 */
function user_rules_event_info() {
  return array(
    'user_insert' => array(
      'label' => t('user has registered'),
      'module' => 'user',
      'arguments' => rules_events_hook_user_arguments(t('registered user')),
      'redirect' => TRUE,
    ),
    'user_update' => array(
      'label' => t('user has updated his account details'),
      'module' => 'user',
      'arguments' => rules_events_hook_user_arguments(t('updated user')) + array('account_unchanged' => array('entity' => 'user', 'label' => t('unchanged user'))),
      'redirect' => TRUE,
    ),
    'user_view' => array(
      'label' => t('user page has been viewed'),
      'module' => 'user',
      'description' => t('Note that if drupal\'s page cache is enabled, this event won\'t be generated for pages served from cache.'),
      'arguments' => rules_events_hook_user_arguments(t('viewed user')),
    ),
    'user_delete' => array(
      'label' => t('user has been deleted'),
      'module' => 'user',
      'arguments' => rules_events_hook_user_arguments(t('deleted user')),
      'redirect' => TRUE,
    ),
    'user_login' => array(
      'label' => t('user has logged in'),
      'module' => 'user',
      'arguments' => array('account' => array('entity' => 'user', 'label' => t('logged in user'))),
      'redirect' => TRUE,
    ),
    'user_logout' => array(
      'label' => t('user has logged out'),
      'module' => 'user',
      'arguments' => array('account' => array('entity' => 'user', 'label' => t('logged out user'))),
      'redirect' => TRUE,
    ),
  );
}

/**
 * Returns the argument definition for the acting user (global $user)
 */
function rules_events_global_user_argument() {
  return array('user' => array('entity' => 'user', 'label' => t('acting user'), 'handler' => 'rules_events_argument_global_user'));
}


/**
 * Returns some arguments suitable for hook_user
 */
function rules_events_hook_user_arguments($account_label) {
  return array(
    'account' => array('entity' => 'user', 'label' => $account_label),
  ) + rules_events_global_user_argument();
}

/**
 * Gets the user account of the "acting" user - which is always the global user
 */
function rules_events_argument_global_user() {
  global $user;
  return user_load(array('uid' => $user->uid));
}


/**
 * Implementation of hook_rules_condition_info()
 */
function user_rules_condition_info() {
  return array(
    'rules_condition_user_comparison' => array(
      'label' => t('Compare two users'),
      'arguments' => array(
        'user1' => array('entity' => 'user', 'label' => t('First user to compare')),
        'user2' => array('entity' => 'user', 'label' => t('Second user to compare')),
      ),
      'help' => t('Evaluates to TRUE, if both compared users are the same user account.'),
      'module' => 'user',
    ),
    'rules_condition_user_hasrole' => array(
      'label' => t('user has role(s)'),
      'arguments' => array(
        'user' => array('entity' => 'user', 'label' => t('user to test')),
      ),
      'help' => t('Whether the user has the selected role(s).'),
      'module' => 'user',
    ),
  );
}

/*
 * A simple user comparison
 */
function rules_condition_user_comparison($user1, $user2) {
  return $user1->uid == $user2->uid;
}

/*
 * Condition user: condition to check whether user has particular roles
 */
function rules_condition_user_hasrole($user, $settings) {
  $roles = $settings['roles'];
  $operation = $settings['operation'];
  
  switch ($operation) {
    case 'OR':
      foreach ($roles as $rid) {
        if (isset($user->roles[$rid])) {
          return TRUE;
        }
      }
      return FALSE;
    case 'AND':
      foreach ($roles as $rid) {
        if (!isset($user->roles[$rid])) {
          return FALSE;
        }
      }
      return TRUE;
  }
}



/**
 * Implementation of hook_rules_action_info 
 */
function user_rules_action_info() {
  return array(
    'rules_action_user_addrole' => array(
      'label' => t('Add user role'),
      'arguments' => array(
        'user' => array('entity' => 'user', 'label' => t('user whos roles should be changed')),
      ),
      'module' => 'user',
    ),
    'rules_action_user_removerole' => array(
      'label' => t('Remove user role'),
      'arguments' => array(
        'user' => array('entity' => 'user', 'label' => t('user whos roles should be changed')),
      ),
      'module' => 'user',
    ),
    'rules_action_load_user' => array(
      'label' => t('Load user by name'),
      'new variables' => array(
        'user_loaded' => array('entity' => 'user', 'label' => t('Loaded user')),
      ),
      'help' => t('Enter an id or a name of the user to load.'),
      'module' => 'user',
     ),
  );
}

/*
 * Action user: adds roles to a particular user
 */
function rules_action_user_addrole($user, $settings) {
  // Get role list (minus the anonymous)
  $role_list = user_roles(1);
  $roles = $settings['roles'];
  foreach ($roles as $rid) {
    $user->roles[$rid] = $role_list[$rid];
  }
  user_save($user, array('roles' => $user->roles));
  // Delete that user's menu cache:
  cache_clear_all($user->uid .':', 'cache_menu', TRUE);
  return array('user' => $user);
}

/*
 * user Action: deletes roles from a given user
 */
function rules_action_user_removerole($user, $settings) {
  $roles = $settings['roles'];
  foreach ($roles as $rid) {
    // If the user has this role, delete it
    if (isset($user->roles[$rid])) {
      unset($user->roles[$rid]);
    }
  }
  user_save($user, array('roles' => $user->roles));
  // Delete that user's menu cache:
  cache_clear_all($user->uid .':', 'cache_menu', TRUE);
  return array('user' => $user);
}

/**
 * Loads a user
 */
function rules_action_load_user($settings, &$arguments, &$log) {
  extract(rules_token_replace_all(array('username'), $settings, $arguments, $log));

  $userarray = array('name' => $username, 'uid' => $userid);
  return array('user_loaded' => user_load(array_filter($userarray)));
}