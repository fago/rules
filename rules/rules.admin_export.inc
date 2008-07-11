<?php
// $Id$

/**
 * @file Rules Import/Export Admin UI
 */

require_once drupal_get_path('module', 'rules') . '/rules.admin.inc';

/**
 * Imports a configuration of rules, rule sets, ..
 */
function rules_admin_form_import() {
  $form = array();
  $form['import'] = array(
    '#type' => 'textarea',
    '#title' => t('Configurations to import'),
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
    foreach ($import as $name => $elements) {
      if (strtolower($data['#module']) == 'rules-ng') {
        //generate a new unique name for this configuration
        $data['#name'] = 'cfg_'. db_next_id('{rules_cfg}_name');
      }
      rules_admin_save_rule($data);
      drupal_set_message(t('Imported rule configuration %label.', array('%label' => rules_get_element_label($data))));
    }
    rules_clear_cache();
  }
  else {
    drupal_set_message(t('Import failed.'), 'error');
  }
}

/**
 * Exports one ore more configurations
 */
function rules_admin_form_export($form_state) {
  $form = array();
  if (!isset($form_state['cfgs'])) {
    $form['cfg'] = array('#tree' => TRUE);
    foreach (rules_get_items() as $name => $info) {
      $form['cfg'][$name] = array(
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
      '#default_value' => var_export($form_state['cfgs'], TRUE),
    );
  }
  return $form;
}

function rules_admin_form_export_submit($form, &$form_state) {
  $cfgs = array();
  foreach (array_filter($form_state['values']['cfg']) as $item_type => $item_names) {
    $items = rules_get_configured_items($item_type);
    $cfgs[$item_type] = array_intersect_key($items, array_filter($item_names));
  }
  if ($cfgs) {
    $form_state['cfgs'] = $cfgs;
  }
  else {
    drupal_set_message(t('Please select the items to export.'), 'error');
  }
  $form_state['rebuild'] = TRUE;
}
