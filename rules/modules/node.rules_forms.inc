<?php
// $Id$
/**
 * @file Rules configuration forms for the node module
 */

/**
 *  Check for content types - Configuration form
 */
function rules_condition_content_is_type_form($settings, &$form) {
  $form['settings']['type'] = array(
    '#type' => 'select',
    '#title' => t('Content types'),
    '#options' => node_get_types('names'),
    '#multiple' => TRUE,
    '#default_value' => isset($settings['type']) ? $settings['type'] : array(),
    '#required' => TRUE,
  );
}

/**
 * Action node publish configuration form
 */
function rules_action_node_publish_form($settings, &$form) {
  $form['settings']['published'] = array(
    '#type' => 'checkbox',
    '#title' => t('Published'),
    '#default_value' => isset($settings['published']) ? $settings['published'] : 0,
  );
}

/**
 * Action node promote configuration form
 */
function rules_action_node_promote_form($settings, &$form) {
  $form['settings']['promote'] = array(
    '#type' => 'checkbox',
    '#title' => t('Promoted to front page'),
    '#default_value' => isset($settings['promote']) ? $settings['promote'] : 0,
  );
}

/**
 * Action node sticky configuration form
 */
function rules_action_node_sticky_form($settings, &$form) {
  $form['settings']['sticky'] = array(
    '#type' => 'checkbox',
    '#title' => t('Sticky at top of lists'),
    '#default_value' => isset($settings['sticky']) ? $settings['sticky'] : 0,
  );
}

/**
 * Action "Add a node" configuration form
 */
function rules_action_add_node_form($settings, &$form) {
  $form['settings']['type'] = array(
    '#type' => 'select',
    '#title' => t('Content type to be used'),
    '#options' => node_get_types('names'),
    '#default_value' => isset($settings['type']) ? $settings['type'] : '',
    '#description' => t('Select a content type that will be created when this action is invoked.'),
    '#required' => TRUE,
  );
  $form['settings']['node_access'] = array(
    '#type' => 'checkbox',
    '#title' => t('Create content only if the given author has access permission to do so'),
    '#default_value' => isset($settings['node_access']) ? $settings['node_access'] : 0,
  );
  $form['settings']['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#default_value' => isset($settings['title']) ? $settings['title'] : '',
    '#description' => t('Enter the title of the newly created content.'),
    '#required' => TRUE,
  );
}

/**
 * Action "Load a node" configuration form
 */
function rules_action_load_node_form($settings, &$form) {
  $form['settings']['nid'] = array(
    '#type' => 'textfield',
    '#title' => t('Content ID'),
    '#default_value' => isset($settings['nid']) ? $settings['nid'] : '',
    '#required' => TRUE,
  );
  $form['settings']['vid'] = array(
    '#type' => 'textfield',
    '#title' => t('Content Revision ID'),
    '#default_value' => isset($settings['vid']) ? $settings['vid'] : '',
    '#description' => t('If you want to load a specific revision, enter it\'s revision id. Else leave it empty to load the current revision.'),
  );
}
