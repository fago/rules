<?php
// $Id$

/**
 * @file Rules Admin UI
 *   Implements rule management and configuration screens.
 */

/**
 * Reaction rules overview
 */
function rules_admin_reaction_overview($form, &$form_state) {
  $form['filter'] = array(
    '#type' => 'fieldset',
    '#title' => t('Filter'),
    '#collapsible' => TRUE,
    '#collapsed' => isset($form_state['values']),
  );
  $form['filter']['event'] = array(
    '#type' => 'select',
    '#title' => t('Filter by event'),
    '#options' => array(0 => '<All>') + RulesPluginUI::getOptions('event_info'),
    '#default_value' => isset($form_state['values']['event']) ? $form_state['values']['event'] : 0,
  );
  $form['filter']['category'] = array(
    '#type' => 'select',
    '#title' => t('Filter by category'),
    '#options' => array(0 => '<All>'),
    '#default_value' => isset($form_state['values']['category']) ? $form_state['values']['category'] : 0,
  );
  $form['submit'] = array('#type' => 'submit', '#value' => t('Filter'));

  $event = !empty($form_state['values']['event']) ? $form_state['values']['event'] : FALSE;
  $category = !empty($form_state['values']['category']) ? $form_state['values']['category'] : FALSE;

  $form['active'] = array(
    '#type' => 'fieldset',
    '#title' => t('Active rules'),
  );
  $conditions = array('event' => $event, 'plugin' => 'reaction rule', 'active' => TRUE);
  $form['active']['table'] = RulesPluginUI::overviewTable(array_filter($conditions), t('There are no matching active reaction rules.'));


  $form['inactive'] = array(
    '#type' => 'fieldset',
    '#title' => t('Inactive rules'),
  );
  $conditions = array_filter(array('event' => $event, 'plugin' => 'reaction rule')) + array('active' => FALSE);
  $form['inactive']['table'] = RulesPluginUI::overviewTable($conditions, t('There are no matching inactive reaction rules.'));

  $form['#submit'][] = 'rules_admin_form_rebuild_submit';
  return $form;
}

/**
 * Submit callback for the reaction rules overview form.
 */
function rules_admin_form_rebuild_submit($form, &$form_state) {
  $form_state['rebuild'] = TRUE;
}

/**
 * Rules overview
 */
function rules_admin_components_overview($form, &$form_state) {
  $form['filter'] = array(
    '#type' => 'fieldset',
    '#title' => t('Filter'),
    '#collapsible' => TRUE,
    '#collapsed' => isset($form_state['values']),
  );
  $form['filter']['category'] = array(
    '#type' => 'select',
    '#title' => t('Filter by category'),
    '#options' => array(0 => '<All>'),
    '#default_value' => isset($form_state['values']['category']) ? $form_state['values']['category'] : 0,
  );
  $form['submit'] = array('#type' => 'submit', '#value' => t('Filter'));
  $category = !empty($form_state['values']['category']) ? $form_state['values']['category'] : FALSE;

  // Get the plugin name usable as component.
  $plugins = array_keys(rules_filter_array(rules_fetch_data('plugin_info'), 'component', TRUE));
  $conditions = array('plugin' => $plugins);
  $form['table'] = RulesPluginUI::overviewTable($conditions, t('There are no rule components.'));
  $form['#submit'][] = 'rules_admin_form_rebuild_submit';
  return $form;
}

/**
 * Define the rule settings
 */
function rules_admin_settings($form, &$form_state) {
  $form['rules_debug'] = array(
    '#type' => 'checkbox',
    '#title' => t('Debug rules evaluation'),
    '#default_value' => FALSE,
    '#description' => t('If activated debugging information is shown when rules are evaluated.'),
  );
  return system_settings_form($form, TRUE);
}

/**
 * Add reaction rules form
 */
function rules_admin_add_reaction_rule($form, &$form_state) {
  $rules_config = rules_reaction_rule();
  $rules_config->form($form, $form_state, array('show settings' => true, 'button' => TRUE));

  $form['settings']['#collapsible'] = FALSE;
  unset($form['elements'], $form['conditions'], $form['add'], $form['events']);

  $form_state += array('rules_config' => $rules_config);
  return $form;
}

/**
 * Form add reaction_rule validation callback.
 */
function rules_admin_add_reaction_rule_validate($form, &$form_state) {
  $form_state['rules_config']->form_validate($form, $form_state);
}

/**
 * Form add reaction_rule submit callback.
 */
function rules_admin_add_reaction_rule_submit($form, &$form_state) {
  $rules_config = $form_state['rules_config'];
  $rules_config->form_submit($form, $form_state);
  $form_state['redirect'] = RulesPluginUI::path($rules_config->name, 'add/event');
}
