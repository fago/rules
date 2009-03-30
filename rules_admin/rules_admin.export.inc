<?php
// $Id$

/**
 * @file Rules Import/Export Admin UI
 */

rules_include('rules_admin');

/**
 * Imports a configuration of rules, rule sets, ..
 */
function rules_admin_form_import() {
  $form = array();
  $form['import'] = array(
    '#type' => 'textarea',
    '#title' => t('Configuration to import'),
    '#description' => t('Just paste your exported configuration here.'),
    '#rows' => 15,
    '#required' => TRUE,
  );
  $form['button'] = array('#type' => 'submit', '#weight' => 10, '#value' => t('Import'));
  return $form;
}

function rules_admin_form_import_submit($form, $form_state) {
  @eval('$import = '. $form_state['values']['import'] .';');
  if (isset($import) && is_array($import) && count($import)) {
    // Check whether it are workflow-ng rules
    if (rules_admin_import_workflow_ng($import)) {
      return;
    }

    foreach ($import as $item_type => $elements) {
      foreach ($elements as $name => $element) {
        //let the item type alter the data before it's saved
        rules_item_type_invoke($item_type, 'import', array(&$name, &$element));
        rules_item_save($item_type, $name, $element);
        drupal_set_message(t('Imported %label.', array('%label' => rules_get_element_label($element))));
      }
    }
    rules_clear_cache();
  }
  else {
    drupal_set_message(t('Import failed.'), 'error');
  }
}

/**
 * Item type callback: Customize to be imported rules
 */
function rules_item_rule_import(&$name, &$rule) {
  $rules = rules_get_configured_items('rules');

  if (!isset($rule['#status']) || $rule['#status'] == 'default') {
    if (!isset($rules[$name])) {
      // This default rule doesn't exist on this system, so we make it custom
      $rule['#status'] = 'custom';
    }
  }
  if (isset($rule['#status']) && $rule['#status'] == 'custom' && (!isset($rules[$name]) || $rules[$name]['#label'] != $rule['#label'] || $rules[$name]['#set'] != $rule['#set'])) {
    if (strpos($name, 'rules_') === 0) {
      // Generate a new unique name for this rule, but only if it's "our rule".
      $name = _rules_admin_rule_get_new_unique_name();
    }
    $rule['#status'] = 'custom';
  }
}
/**
 * Item type callback: Customize to be imported rule sets
 */
function rules_item_rule_set_import(&$name, &$rule_set) {

  if (!isset($rule_set['status']) || $rule_set['status'] == 'default') {
    $sets = rules_get_configured_items('rule_sets');

    if (!isset($sets[$name])) {
      // This default rule set doesn't exist on this system, so we make it custom
      $rule_set['status'] = 'custom';
    }
  }
}

/**
 * Tries to import workflow-ng rules.
 *
 * @return TRUE, only if workflow-ng rules are detected.
 */
function rules_admin_import_workflow_ng($import) {
  foreach ($import as $name => $cfg) {
    if (count(element_children($cfg)) == count($cfg)) {
      // This is no workflow-ng rule, so exit
      return FALSE;
    }
    rules_include('rules');

    // Load the install and form include files, as there modules
    // may provide upgrade information.
    module_load_all_includes('install');
    rules_include('rules_forms');

    $rule = rules_import_workflow_ng_rule($name, $cfg);
    if ($rule) {
      // Handle rule format upgrades as the workflow-ng import just returns rules of the initial format
      $rule = rules_rule_format_upgrade($rule);

      rules_item_save('rules', $name, $rule);
      drupal_set_message(t('Successfully imported the workflow-ng rule %label.', array('%label' => $rule['#label'])));
    }
    else {
      drupal_set_message(t('Failed importing the workflow-ng rule %label.', array('%label' => $rule['#label'])), 'error');
    }
  }
  rules_clear_cache();
  return TRUE;
}



/**
 * Exports one ore more configurations
 */
function rules_admin_form_export($form_state) {
  $form = array();
  if (!isset($form_state['export'])) {
    $form['export'] = array('#tree' => TRUE);
    foreach (rules_get_items() as $name => $info) {
      $form['export'][$name] = array(
        '#type' => 'select',
        '#title' => t('Select the %label to export', array('%label' => $info['label'])),
        '#options' => rules_admin_get_grouped_labels(rules_get_configured_items($name)),
        '#multiple' => TRUE,
        '#default_value' => 0,
      );
    }
    $form['button'] = array('#type' => 'submit', '#weight' => 10, '#value' => t('Export'));
  }
  else {
    //show a textarea containg the exported configs
    $form['result'] = array(
      '#type' => 'textarea',
      '#title' => t('Exported rule configurations'),
      '#description' => t('Copy these data and paste them into the import page, to import.'),
      '#rows' => 15,
      '#attributes' => array('readonly' => 'readonly'),
      '#default_value' => var_export($form_state['export'], TRUE),
    );
  }
  return $form;
}

function rules_admin_form_export_submit($form, &$form_state) {
  $export = array();
  foreach (array_filter($form_state['values']['export']) as $item_type => $item_names) {
    $items = rules_get_configured_items($item_type);
    $export[$item_type] = array_intersect_key($items, array_filter($item_names));
    //allow item specific adaption
    foreach ($export[$item_type] as $item_name => $item) {
      rules_item_type_invoke($item_type, 'export', array($item_name, &$export[$item_type][$item_name], &$export));
    }
  }
  if ($export) {
    $form_state['export'] = $export;
  }
  else {
    drupal_set_message(t('Please select the items to export.'), 'error');
  }
  $form_state['rebuild'] = TRUE;
}

/**
 * Item type callback: When exporting a rule set, add its rules to the export.
 */
function rules_item_rule_set_export($set_name, &$rule_set, &$export) {
  $rules = rules_get_configured_items('rules');
  foreach ($rules as $name => $rule) {
    if ($rule['#set'] == $set_name) {
      $export['rules'][$name] = $rule;
    }
  }
}
