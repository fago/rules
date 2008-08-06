<?php
// $Id$
/**
 * @file Rules configuration forms for the node module
 */

/**
 * Condition: Check for content types - Configuration form
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
 * Condition: Check for selected content types - Label callback
 */
function rules_condition_content_is_type_label($settings) {
  $names = array_intersect_key(node_get_types('names'), $settings['type']);
  return t('Content is @type', array('@type' => implode(' or ', $names)));
}

/**
 * Action set node author - Label callback
 */
function rules_action_node_set_author_label($settings, $argument_labels) {
  return t('Set the author of @node to @author', $argument_labels);
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
}

function rules_action_add_node_variable_label($settings) {
  return t('New content of type @type', array('@type' => $settings['type']));
}

/**
 * Action "Load a node" variable label callback
 */
function rules_action_load_node_variable_label($settings) {
  return t('Content with id @id', array('@id' => $settings['nid']));
}


/**
 * The following functions help converting node actions
 * when upgrading from workflow-ng
 */

function workflow_ng_action_node_publish_upgrade(&$element) {
  $element['#name'] = $element['#settings']['published'] ? 'rules_core_node_publish_action' : 'rules_core_node_unpublish_action';
  $element['#settings'] = array('auto_save' => TRUE);
}
function workflow_ng_action_node_promote_upgrade(&$element) {
  $element['#name'] = $element['#settings']['promote'] ? 'rules_core_node_promote_action' : 'rules_core_node_unpromote_action';
  $element['#settings'] = array('auto_save' => TRUE);
}
function workflow_ng_action_node_sticky_upgrade(&$element) {
  $element['#name'] = $element['#settings']['sticky'] ? 'rules_core_node_make_sticky_action' : 'rules_core_node_make_unsticky_action';
  $element['#settings'] = array('auto_save' => TRUE);
}
