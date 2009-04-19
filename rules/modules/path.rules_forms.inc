<?php
// $Id$


/**
 * @file Rules configuration forms for the path module
 */
function rules_condition_url_has_alias_form($settings, &$form) {
  $settings += array('src' => '');
  $form['settings']['src'] = array(
    '#type' => 'textfield',
    '#title' => t('Existing system path'),
    '#default_value' => $settings['src'],
    '#maxlength' => 256,
    '#description' => t('Specify the existing path for which you want to check if an URL alias exists.'),
    '#field_prefix' => url('', array('absolute' => TRUE)) . (variable_get('clean_url', 0) ? '' : '?q='),
    '#required' => TRUE,
  );
}

function rules_condition_alias_exists_form($settings, &$form) {
  $settings += array('dst' => '', 'replace' => '-');
  $form['settings']['dst'] = array(
    '#type' => 'textfield',
    '#title' => t('Path alias'),
    '#default_value' => $settings['dst'],
    '#maxlength' => 256,
    '#description' => t('Specify the path alias which you want to check if it already exists.'),
    '#field_prefix' => url('', array('absolute' => TRUE)) . (variable_get('clean_url', 0) ? '' : '?q='),
    '#required' => TRUE,
  );
  $form['settings']['replace'] = array(
    '#type' => 'textfield',
    '#title' => t('Before checking, replace non ascii characters with'),
    '#default_value' => $settings['replace'],
    '#maxlength' => 5,
    '#size' => 5,
    '#description' => t('Leave this textfield empty to disable the replacement of non ascii characters.'),
  );
}

function rules_action_path_alias_form($settings, &$form) {
  $settings += array('dst' => '', 'src' => '', 'replace' => '-');
  $form['settings']['src'] = array(
    '#type' => 'textfield',
    '#title' => t('Existing system path'),
    '#default_value' => $settings['src'],
    '#maxlength' => 256,
    '#description' => t('Specify the existing path you wish to alias. For example: node/28, forum/1, taxonomy/term/1+2.') .' '. t('Leave it empty to delete URL aliases pointing to the given path alias.'),
    '#field_prefix' => url('', array('absolute' => TRUE)) . (variable_get('clean_url', 0) ? '' : '?q='),
  );
  $form['settings']['dst'] = array(
    '#type' => 'textfield',
    '#title' => t('Path alias'),
    '#default_value' => $settings['dst'],
    '#maxlength' => 256,
    '#description' => t('Specify an alternative path by which this data can be accessed. For example, type "about" when writing an about page. Use a relative path and do not add a trailing slash or the URL alias will not work.') .' '. t('Leave it empty to delete URL aliases pointing to the given system path.'),
    '#field_prefix' => url('', array('absolute' => TRUE)) . (variable_get('clean_url', 0) ? '' : '?q='),
  );
  $form['settings']['replace'] = array(
    '#type' => 'textfield',
    '#title' => t('Replace non ascii characters with'),
    '#default_value' => $settings['replace'],
    '#maxlength' => 5,
    '#size' => 5,
    '#description' => t('Leave this textfield empty to disable the replacement of non ascii characters.'),
  );
}

function rules_action_path_alias_validate($form, $form_state) {
  if (empty($form_state['values']['settings']['src']) && empty($form_state['values']['settings']['dst'])) {
    form_set_error('settings][src', t('You have to enter at least eiter an existing system path or a path alias.'));
  }
}

function rules_action_node_path_alias_form($settings, &$form) {
  rules_action_path_alias_form($settings, $form);
  // Remove the unneeded src field.
  unset($form['settings']['src']);
}

function rules_action_node_path_alias_label($settings, $argument_labels) {
  return t("Create or delete @node's URL alias", $argument_labels);
}

function rules_action_node_path_alias_help() {
  return t('This action only works if the acting user has %perm1 or %perm2 permsissions. If this does not suit, use the generic "Create or delete an URL alias" action together with the existing system path "node/{ID}".', array('%perm1' => t('create url aliases'), '%perm2' => t('administer url aliases')));
}

