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
  );
  $form['filter']['#id'] = 'rules-filter-form';
  $form['filter']['#attached']['css'][] = drupal_get_path('module', 'rules') . '/ui/rules.ui.css';
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
  $form['filter']['submit'] = array('#type' => 'submit', '#value' => t('Filter'));

  $event = !empty($form_state['values']['event']) ? $form_state['values']['event'] : FALSE;
  $category = !empty($form_state['values']['category']) ? $form_state['values']['category'] : FALSE;

  $conditions = array('event' => $event, 'plugin' => 'reaction rule', 'active' => TRUE);
  $form['active'] = RulesPluginUI::overviewTable(array_filter($conditions), t('There are no matching active reaction rules.'));
  $form['active']['#prefix'] = '<h4>' . t('Active rules') . '</h4>';

  $conditions = array_filter(array('event' => $event, 'plugin' => 'reaction rule')) + array('active' => FALSE);
  $form['inactive'] = RulesPluginUI::overviewTable($conditions, t('There are no matching inactive reaction rules.'));
  $form['inactive']['#prefix'] = '<h4>' . t('Inactive rules') . '</h4>';

  $form['filter']['#collapsed'] = isset($form_state['values']) || (count($form['active']['#rows']) + count($form['inactive']['#rows'])) < 20;
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
  );
  $form['filter']['#id'] = 'rules-filter-form';
  $form['filter']['#attached']['css'][] = drupal_get_path('module', 'rules') . '/ui/rules.ui.css';
  $form['filter']['category'] = array(
    '#type' => 'select',
    '#title' => t('Filter by category'),
    '#options' => array(0 => '<All>'),
    '#default_value' => isset($form_state['values']['category']) ? $form_state['values']['category'] : 0,
  );
  $form['filter']['submit'] = array('#type' => 'submit', '#value' => t('Filter'));

  $category = !empty($form_state['values']['category']) ? $form_state['values']['category'] : FALSE;

  // Get the plugin name usable as component.
  $plugins = array_keys(rules_filter_array(rules_fetch_data('plugin_info'), 'component', TRUE));
  $conditions = array('plugin' => $plugins);
  $form['table'] = RulesPluginUI::overviewTable($conditions, t('There are no rule components.'));

  $form['filter']['#collapsed'] = isset($form_state['values']) || count($form['table']['#rows']) < 20;
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
    '#default_value' => variable_get('rules_debug', FALSE),
    '#description' => t('If activated debugging information is shown when rules are evaluated.'),
  );
  return system_settings_form($form);
}

/**
 * Add reaction rules form
 */
function rules_admin_add_reaction_rule($form, &$form_state) {
  $rules_config = rules_reaction_rule();
  $rules_config->form($form, $form_state, array('show settings' => TRUE, 'button' => TRUE));

  $form['settings']['#collapsible'] = FALSE;
  $form['settings']['#type'] = 'container';
  $form['settings']['label']['#default_value'] = '';

  // Hide the rule elements stuff for now.
  foreach (array('elements', 'conditions', 'add', 'events') as $key) {
    $form[$key]['#access'] = FALSE;
  }
  foreach (array('active', 'weight') as $key) {
    $form['settings'][$key]['#access'] = FALSE;
  }
  // Incorporate the form to add the first event.
  module_load_include('inc', 'rules', 'ui/ui.forms');
  $form['settings'] += rules_ui_add_event(array(), $form_state, $rules_config);
  unset($form['settings']['submit']);
  $form['submit']['#value'] = t('Save');

  $form_state += array('rules_config' => $rules_config);
  $form['#validate'][] = 'rules_ui_add_event_validate';
  return $form;
}

/**
 * Form add reaction_rule submit callback.
 */
function rules_admin_add_reaction_rule_submit($form, &$form_state) {
  $rules_config = $form_state['rules_config'];
  $rules_config->form_submit($form, $form_state);
  $form_state['redirect'] = RulesPluginUI::path($rules_config->name);
}
