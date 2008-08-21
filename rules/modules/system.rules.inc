<?php
// $Id$

/**
 * @file rules integration for the system module
 */

/**
 * Implementation of hook_rules_event_info().
 */
function system_rules_event_info() {
  return array(
    'init' => array(
      'label' => t('User is going to view a page'),
      'module' => 'System',
      'description' => t('Note that if drupal\'s page cache is enabled, this event won\'t be generated for pages served from cache.'),
      'arguments' => rules_events_global_user_argument(),
    ),
    'cron' => array(
      'label' => t('Cron maintenance tasks are performed'),
      'module' => 'System',
      'arguments' => array(),
    ),
  );
}


/**
 * Implementation of hook_rules_action_info().
 */
function system_rules_action_info() {
  return array(
    'rules_action_drupal_message' => array(
      'label' => t('Show a configurable message on the site'),
      'module' => 'System',
      'eval input' => array('message'),
    ),
    'rules_action_set_breadcrumb' => array(
      'label' => t('Set breadcrumb'),
      'module' => 'System',
      'eval input' => array('titles', 'paths'),
    ),
    'rules_action_mail_to_user' => array(
      'label' => t('Send a mail to a user'),
      'arguments' => array(
        'user' => array('type' => 'user', 'label' => t('Recipient')),
      ),
      'module' => 'System',
      'eval input' => array('subject', 'message', 'from'),
    ),
    'rules_action_mail' => array(
      'label' => t('Send a mail to an arbitrary mail address'),
      'module' => 'System',
      'eval input' => array('subject', 'message', 'from', 'to'),
    ),
    'rules_action_mail_to_users_of_role' => array(
      'label' => t('Send a mail to all users of a role'),
      'module' => 'System',
      'eval input' => array('subject', 'message', 'from'),
    ),
    'rules_action_drupal_goto' => array(
      'label' => t('Page redirect'),
      'module' => 'System',
      'eval input' => array('path', 'query', 'fragment'),
      'help' => t('Enter a Drupal path, path alias, or external URL to redirect to. Enter (optional) queries after "?" and (optional) anchor after "#".'),
    ),
    'rules_action_watchdog' => array(
      'label' => t('Log to watchdog'),
      'module' => 'System',
      'eval input' => array('type', 'message', 'link'),
    ),
  );
}

function rules_action_drupal_message($settings) {
  drupal_set_message($settings['message'], $settings['error'] ? 'error' : 'status');
}

/**
 * Action Implementation: Set breadcrumb.
 */
function rules_action_set_breadcrumb($settings) {
  $titles = explode("\n", $settings['titles']);
  $paths = explode("\n", $settings['paths']);

  $trail = array(l(t('Home'), ''));
  for ($i = 0; $i < count($titles); $i++) {
    // Skip empty titles
    if ($title = trim($titles[$i])) {
      // Output plaintext instead of a link if there is a title
      // without a path.
      $path = trim($paths[$i]);
      if (strlen($path) > 0 && $path != '<none>') {
        $trail[] = l($title, trim($paths[$i]));
      }
      else {
        $trail[] = check_plain($title);
      }
    }
  }
  drupal_set_breadcrumb($trail);
}

/**
 * Action Implementation: Send a mail to a user.
 */
function rules_action_mail_to_user($user, $settings) {
  // We also handle $settings['to'] if it's set.
  $to = isset($settings['to']) ? str_replace(array("\r", "\n"), '', $settings['to']) : $user->mail;
  $from = isset($settings['from']) ? str_replace(array("\r", "\n"), '', $settings['from']) : NULL;

  $message = drupal_mail('rules', 'rules_action_mail', $to, language_default(), $settings, $from);
  if ($message['result']) {
    watchdog('rules', 'Successfully sent email to %recipient', array('%recipient' => $to));
  }
}

/**
 * Implementation of hook_mail().
 *
 * Set's the message subject and body as configured in the $settings of the action.
 */
function rules_mail($key, &$message, $settings) {

  $message['subject'] .= str_replace(array("\r", "\n"), '', $settings['subject']);
  $message['body'][] = drupal_html_to_text($settings['message']);
}



/**
 * Action Implementation: rules_action_mail
 * This action makes use of the rules_action_mail_to_user action implementation.
 */
function rules_action_mail($settings) {
  rules_action_mail_to_user(NULL, $settings);
}


/**
 * Action: Send mail to all users of a specific role group(s).
 */
function rules_action_mail_to_users_of_role($settings) {
  $recipients = $settings['recipients'];
  $from = isset($settings['from']) ? str_replace(array("\r", "\n"), '', $settings['from']) : NULL;

  // All authenticated users, which is everybody.
  if (in_array(DRUPAL_AUTHENTICATED_RID, $recipients)) {
    $result = db_query('SELECT mail FROM {users} WHERE uid > 0');
  }
  else {
    $rids = implode(',', $recipients);
    // Avoid sending emails to members of two or more target role groups.
    $result = db_query('SELECT DISTINCT u.mail FROM {users} u INNER JOIN {users_roles} r ON u.uid = r.uid WHERE r.rid IN ('. $rids .')');
  }

  // Now, actually send the mails.
  $message = array('result' => TRUE);
  while (($account = db_fetch_object($result)) && $message['result']) {
    $message = drupal_mail('rules', 'rules_action_role_mail', $account->mail, language_default(), $settings, $from);
  }
  if ($message['result']) {
    $roles = array_intersect_key(user_roles(TRUE), drupal_map_assoc($recipients));
    watchdog('rules', 'Successfully sent email to the role(s) %roles.', array('%roles' => implode(', ', $roles)));
  }
}

/**
 * Action Implementation: Page redirect.
 */
function rules_action_drupal_goto($settings) {
  extract( $settings );

  if ($force) {
    if (strpos($query, 'destination') === FALSE) {
      if (!empty($query)) {
        $query .= '&';
      }
      $query .= $_REQUEST['destination'] ? 'destination='. urlencode($_REQUEST['destination']) : NULL;
    }
    unset($_REQUEST['destination']);
  }
  if ($override) {
    // Override the destination parameter, so another drupal_goto goes to our url :)
    $_REQUEST['destination'] = urlencode($path) . ($query ? '?'. urlencode($query) : '') . ($fragment ? '#'. urlencode($fragment) : '');
  }
  else {
    drupal_goto($path, $query ? $query : NULL, $fragment ? $fragment : NULL);
  }
}

/**
 * Action Implementation: Log to watchdog.
 */
function rules_action_watchdog($settings) {
  watchdog($settings['type'], $settings['message'], NULL, $settings['severity'], $settings['link']);
}

/**
 * Implementation of hook_rules_action_info_alter().
 *
 * Adapts the action info of the core actions to better fit for rules.
 */
function system_rules_action_info_alter(&$actions) {
  // The goto action doesn't work (object!?) and is dangeroures - so we provide our own.
  unset($actions['rules_core_system_goto_action']);
  // The next two rely heavily on contexts - which we handle already in rules.
  // So we provide our own actions.
  unset($actions['rules_core_system_message_action']);
  unset($actions['rules_core_system_send_email_action']);
}
