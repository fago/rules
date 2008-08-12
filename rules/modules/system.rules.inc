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

