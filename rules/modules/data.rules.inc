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
      'label' => t('decimal number'),
      'class' => 'RulesDecimalWrapper',
      'parent' => 'text',
    ),
   'integer' => array(
      'label' => t('integer'),
      'class' => 'RulesIntegerWrapper',
      'parent' => 'decimal',
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
      'parent' => 'text',
    ),
   'list' => array(
      'label' => t('list'),
      'class' => 'RulesDataListWrapper',
    ),
   'entity' => array(
      'label' => t('any entity'),
      'class' => 'RulesDataEntityWrapper',
    ),
    'node' => array(
      'label' => t('content'),
      'class' => 'RulesDataEntityWrapper',
      'parent' => 'entity',
      'extenders' => array(
        'RulesDataCRUDInterface' => array('class' => 'RulesDataEntityExtender')
      ),
    ),
    'user' => array(
      'label' => t('user'),
      'class' => 'RulesDataEntityWrapper',
      'parent' => 'entity',
      'extenders' => array(
        'RulesDataSavableInterface' => array('class' => 'RulesDataEntityExtender'),
        'RulesDataCreatableInterface' => array('class' => 'RulesDataEntityExtender'),
      ),
    ),
    'file' => array(
      'label' => t('user'),
      'class' => 'RulesDataEntityWrapper',
      'parent' => 'entity',
      'extenders' => array(
        'RulesDataSavableInterface' => array('class' => 'RulesDataEntityExtender'),
        'RulesDataDeletableInterface' => array('class' => 'RulesDataEntityExtender'),
      ),
    ),
    'comment' => array(
      'label' => t('taxonony vocabulary'),
      'class' => 'RulesDataEntityWrapper',
      'parent' => 'entity',
      'extenders' => array(
        'RulesDataCRUDInterface' => array('class' => 'RulesDataEntityExtender')
      ),
    ),
    'taxonomy_term' => array(
      'label' => t('taxonony term'),
      'class' => 'RulesDataEntityWrapper',
      'parent' => 'entity',
      'extenders' => array(
        'RulesDataCRUDInterface' => array('class' => 'RulesDataEntityExtender')
      ),
    ),
    'taxonomy_vocabulary' => array(
      'label' => t('taxonony vocabulary'),
      'class' => 'RulesDataEntityWrapper',
      'parent' => 'entity',
      'extenders' => array(
        'RulesDataCRUDInterface' => array('class' => 'RulesDataEntityExtender')
      ),
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
      'base' => 'rules_action_data_set',
      'callbacks' => array(
        'validate' => 'rules_data_parameter_validation',
      ),
    ),
    'data_fetch' => array(
      'label' => t('Fetch data'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Data type'),
          'description' => t('Specify the type of the data that should be fetched.'),
         ),
         // Further needed parameter depends on the type.
      ),
      'provides' => array(
        'data' => array('type' => 'unknown', 'label' => t('Fetched data')),
      ),
      'group' => t('Data'),
      'base' => 'rules_action_data_fetch',
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
      'base' => 'rules_condition_data_is',
      'callbacks' => array(
        'validate' => 'rules_data_parameter_validation',
      ),
    ),
  );
}

/**
 * @}
 */
