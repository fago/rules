<?php
// $Id$

/**
 * @file Rules core integration providing data types and conditions and
 * actions to invoke configured components.
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implements hook_rules_file_info() on behalf of the pseudo rules_core module.
 *
 * @see rules_core_modules()
 */
function rules_rules_core_file_info() {
  return array('modules/rules_core.eval');
}

/**
 * Implements hook_rules_data_info() on behalf of the pseudo rules_core module.
 *
 * @see rules_core_modules()
 */
function rules_rules_core_data_info() {
  $return = array(
    'text' => array(
      'label' => t('text'),
      'ui class' => 'RulesDataUIText',
    ),
    // A formatted text as used by entity metadata.
    'text_formatted' => array(
      'label' => t('formatted text'),
      'ui class' => 'RulesDataUITextFormatted',
      'wrap' => TRUE,
      'property info' => entity_metadata_text_formatted_properties(),
    ),
   'decimal' => array(
      'label' => t('decimal number'),
      'parent' => 'text',
      'ui class' => 'RulesDataUIDecimal',
    ),
   'integer' => array(
      'label' => t('integer'),
      'class' => 'RulesIntegerWrapper',
      'parent' => 'decimal',
      'ui class' => 'RulesDataUIInteger',
    ),
   'date' => array(
      'label' => t('date'),
      'ui class' => 'RulesDataUIDate',
    ),
   'duration' => array(
      'label' => t('duration'),
      'parent' => 'integer',
      'ui class' => 'RulesDataUIDuration',
    ),
   'boolean' => array(
      'label' => t('truth value'),
      'ui class' => 'RulesDataUIBoolean',
    ),
   'uri' => array(
      'label' => t('URI'),
      'parent' => 'text',
      // Clean inserted tokens with "rawurlencode".
      'cleaning callback' => 'rawurlencode',
      'ui class' => 'RulesDataUIURI',
    ),
   'list' => array(
      'label' => t('list'),
      'wrap' => TRUE,
    ),
   'list<text>' => array(
      'label' => t('list of text'),
      'ui class' => 'RulesDataUIListText',
      'wrap' => TRUE,
    ),
   'list<integer>' => array(
      'label' => t('list of integer'),
      'ui class' => 'RulesDataUIListInteger',
      'wrap' => TRUE,
    ),
   'entity' => array(
      'label' => t('any entity'),
    )
  );
  foreach (entity_get_info() as $type => $info) {
    if (!empty($info['label'])) {
      $return[$type] = array(
        'label' => $info['label'],
        'parent' => 'entity',
        'wrap' => TRUE,
      );
    }
  }
  return $return;
}

/**
 * Implements hook_rules_evaluator_info() on behalf of the pseudo rules_core
 * module.
 *
 * @see rules_core_modules()
 */
function rules_rules_core_evaluator_info() {
  return array(
    // Process strtotime() inputs to timestamps.
    'date' => array(
      'class' => 'RulesDateInputEvaluator',
      'type' => 'date',
      'weight' => -10,
     ),
    // Post-process any input value to absolute URIs.
    'uri' => array(
      'class' => 'RulesURIInputEvaluator',
      'type' => 'uri',
      'weight' => 50,
     ),
  );
}

/**
 * Implements hook_rules_data_processor_info() on behalf of the pseudo
 * rules_core module.
 *
 * @see rules_core_modules()
 */
function rules_rules_core_data_processor_info() {
  return array(
    'date_offset' => array(
      'class' => 'RulesDateOffsetProcessor',
      'type' => 'date',
      'weight' => -2,
     ),
  );
}

/**
 * Implements hook_rules_condition_info() on behalf of the pseudo rules_core
 * module.
 *
 * @see rules_core_modules()
 */
function rules_rules_core_condition_info() {
  $defaults = array(
    'group' => t('Rules components'),
    'base' => 'rules_element_invoke_component',
    'named parameter' => TRUE,
  );
  $items = array();
  foreach (rules_get_components(FALSE, 'condition') as $name => $config) {
    $items['component_' . $name] = $defaults + array(
      'label' => $config->label(),
      'parameter' => $config->argumentInfo(),
    );
    $items['component_' . $name]['#config_name'] = $name;
  }
  return $items;
}

/**
 * Implements hook_rules_action_info() on behalf of the pseudo rules_core
 * module.
 *
 * @see rules_core_modules()
 */
function rules_rules_core_action_info() {
  $defaults = array(
    'group' => t('Rules components'),
    'base' => 'rules_element_invoke_component',
    'named parameter' => TRUE,
  );
  $items = array();
  foreach (rules_get_components(FALSE, 'action') as $name => $config) {
    $items['component_' . $name] = $defaults + array(
      'label' => $config->label(),
      'parameter' => $config->argumentInfo(),
      'provides' => $config->providesVariables(),
    );
    $items['component_' . $name]['#config_name'] = $name;
  }
  return $items;
}

/**
 * @}
 */
