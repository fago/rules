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
