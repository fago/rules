<?php
// $Id$
/**
 * @file Rules configuration forms for the system module
 */

/**
 * Action drupal message configuration form.
 */
function rules_action_drupal_message_form($settings, &$form) {
  $form['settings']['message'] = array(
    '#type' => 'textarea',
    '#title' => t('Message'),
    '#default_value' => isset($settings['message']) ? $settings['message'] : '',
    '#description' => t('The message that should be displayed.'),
  );
  $form['settings']['error'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display as error message'),
    '#default_value' => isset($settings['error']) ? $settings['error'] : '',
  );
}

/**
 * Action "Set breadcrumb" configuration form
 */
function rules_action_set_breadcrumb_form($settings = array(), &$form) {
  $settings += array('titles' => '', 'paths' => '');
  $form['settings']['titles'] = array(
    '#type' => 'textarea',
    '#title' => t('Titles'),
    '#required' => TRUE,
    '#description' => t('A list of titles for the breadcrumb links, one on each line.'),
    '#default_value' => $settings['titles'],
  );
  $form['settings']['paths'] = array(
    '#type' => 'textarea',
    '#title' => t('Paths'),
    '#required' => TRUE,
    '#description' => t('A list of Drupal paths for the breadcrumb links, one on each line.'),
    '#default_value' => $settings['paths'],
  );
}

/**
 * Action "Send a mail to a user" configuration form
 */
function rules_action_mail_to_user_form($settings = array(), &$form) {
  $settings += array('from' => '', 'subject' => '', 'message' => '');
  $form['settings']['from'] = array(
    '#type' => 'textfield',
    '#title' => t('Sender'),
    '#default_value' => $settings['from'],
    '#description' => t('The mail\'s "from" address. Leave it empty to use the site-wide configured address.'),
  );
  $form['settings']['subject'] = array(
    '#type' => 'textfield',
    '#title' => t('Subject'),
    '#default_value' => $settings['subject'],
    '#description' => t('The mail\'s subject.'),
  );
  $form['settings']['message'] = array(
    '#type' => 'textarea',
    '#title' => t('Message'),
    '#default_value' => $settings['message'],
    '#description' => t('The mail\'s message body.'),
  );
}

/**
 * Action "Send a mail to an arbitrary mail address" configuration form
 */
function rules_action_mail_form($settings = array(), &$form) {
  $form['settings']['to'] = array(
    '#type' => 'textfield',
    '#title' => t('Recipient'),
    '#default_value' => isset($settings['to']) ? $settings['to'] : '',
    '#description' => t('The mail\'s recipient address. You may separate multiple addresses with ",".'),
    '#required' => TRUE,
    '#weight' => -1,
  );
  rules_action_mail_to_user_form($settings, $form);
}
