<?php
// $Id$

/**
 * @file General data related rules integration
 *
 * @addtogroup rules
 * @{
 */

/**
 * Implements hook_rules_file_info() on behalf of the pseudo data module.
 * @see rules_core_modules()
 */
function rules_data_file_info() {
  return array('modules/data.eval');
}

/**
 * Implements hook_rules_data_info() on behalf of the pseudo data module.
 * @see rules_core_modules()
 */
function rules_data_data_info() {
  return array(
    'text' => array(
      'label' => t('text'),
      'class' => 'RulesTextWrapper',
    ),
   'decimal' => array(
      'label' => t('decimal'),
      'class' => 'RulesDecimalWrapper',
    ),
   'integer' => array(
      'label' => t('integer'),
      'class' => 'RulesIntegerWrapper',
    ),
   'date' => array(
      'label' => t('date'),
      'class' => 'RulesDateWrapper',
    ),
   'duration' => array(
      'label' => t('duration'),
      'class' => 'RulesDurationWrapper',
    ),
   'boolean' => array(
      'label' => t('truth value'),
      'class' => 'RulesBooleanWrapper',
    ),
   'uri' => array(
      'label' => t('URI'),
      'class' => 'RulesURIWrapper',
    ),
   'entity' => array(
      'label' => t('any entity'),
      'class' => 'RulesEntityWrapper',
    ),
  );
}

/**
 * Implements hook_rules_action_info() on behalf of the pseudo data module.
 * @see rules_core_modules()
 */
function rules_data_action_info() {
  return array(
    'data_set' => array(
      'label' => t('Modify data'),
      'parameter' => array(
        'data' => array(
          'type' => '*',
          'label' => t('Data to modify'),
          'description' => t('Specify the data to be modified using a data selector, e.g. "node:author:name".'),
          'save' => TRUE,
         ),
        'value' => array(
          'type' => '*',
          'label' => t('Data value'),
          'description' => t('The new value to set for the selected data.'),
        ),
      ),
      'group' => t('Data'),
      'callbacks' => array(
        'execute' => 'rules_action_data_set',
        'validate' => 'rules_data_parameter_validation',
      ),
    ),
  );
}

/**
 * Implements hook_rules_condition_info() on behalf of the pseudo data module.
 * @see rules_core_modules()
 */
function rules_data_condition_info() {
  return array(
    'data_is' => array(
      'label' => t('Data comparison'),
      'parameter' => array(
        'data' => array(
          'type' => '*',
          'label' => t('Data to compare'),
          'description' => t('Specify the data to be compared using a data selector, e.g. "node:author:name".'),
          'save' => TRUE,
         ),
        'op' => array(
          'type' => 'text',
          'label' => t('Operator'),
          'description' => t('The comparison operator.'),
          'optional' => TRUE,
          'default value' => '=',
        ),
        'value' => array(
          'type' => '*',
          'label' => t('Data value'),
          'description' => t('The value to compare the data with.'),
        ),
      ),
      'group' => t('Data'),
      'callbacks' => array(
        'execute' => 'rules_condition_data_is',
        'validate' => 'rules_data_parameter_validation',
      ),
    ),
  );
}

/**
 * @}
 */
