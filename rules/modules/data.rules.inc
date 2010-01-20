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
  $return['data_set'] = array(
    'label' => t('Modify data'),
    'parameter' => array(
      'data' => array(
        'type' => '*',
        'label' => t('Data to modify'),
        'description' => t('Specify the data to be modified using a data selector, e.g. "node:author:name".'),
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
  );
  $cache = rules_get_cache();
  // Get an array of types that are savable.
  foreach ($cache['data_info'] as $type => $info) {
    if (RulesExtendable::itemFacesAs($info, 'RulesDataIdentifiableInterface')) {
      $types[] = $type;
    }
  }
  if (!empty($types)) {
    $return['data_fetch'] = array(
      'label' => t('Fetch data'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Data type'),
          'options list' => $types,
          'description' => t('Specify the type of the data that should be fetched.'),
         ),
         // Further needed parameter depends on the type.
      ),
      'provides' => array(
        'data_fetched' => array('type' => 'unknown', 'label' => t('Fetched data')),
      ),
      'group' => t('Data'),
      'base' => 'rules_action_data_fetch',
    );
  }
  // Get an array of types that are savable.
  foreach ($cache['data_info'] as $type => $info) {
    if (RulesExtendable::itemFacesAs($info, 'RulesDataSavableInterface')) {
      $types[] = $type;
    }
  }
  if (!empty($types)) {
    $return['data_save'] = array(
      'label' => t('Save data'),
      'parameter' => array(
        'data' => array(
          'type' => $types,
          'label' => t('Data to save'),
          'description' => t('The data, which should be saved permanently.'),
          'save' => TRUE,
         ),
         'immediate' => array(
           'type' => 'boolean',
           'label' => t('Force saving immediately'),
           'description' => t('Usually saving is postponed till the end of the evaluation, so that multiple saves can be fold into one. If this set, saving is forced to happen immediately.'),
           'default value' => FALSE,
           'optional' => TRUE,
         )
      ),
      'group' => t('Data'),
      'base' => 'rules_action_data_save',
      'callbacks' => array(
        'validate' => 'rules_data_parameter_validation',
      ),
    );
  }
  // Get an array of types that are deletable.
  foreach ($cache['data_info'] as $type => $info) {
    if (RulesExtendable::itemFacesAs($info, 'RulesDataDeletableInterface')) {
      $types[] = $type;
    }
  }
  if (!empty($types)) {
    $return['data_delete'] = array(
      'label' => t('Delete data'),
      'parameter' => array(
        'data' => array(
          'type' => $types,
          'label' => t('Data to delete'),
          'description' => t('The data, which should be deleted permanently.'),
         ),
      ),
      'group' => t('Data'),
      'base' => 'rules_action_data_delete',
      'callbacks' => array(
        'validate' => 'rules_data_parameter_validation',
      ),
    );
  }
  // Get an array of types that are creatable.
  foreach ($cache['data_info'] as $type => $info) {
    if (RulesExtendable::itemFacesAs($info, 'RulesDataCreatableInterface')) {
      $types[] = $type;
    }
  }
  if (!empty($types)) {
    $return['data_create'] = array(
      'label' => t('Create new data'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Data type'),
          'options list' => $types,
          'description' => t('Specify the type of the data that should be created.'),
         ),
         // Further needed parameter depends on the type.
      ),
      'provides' => array(
        'data_created' => array(
          'type' => 'unknown',
          'label' => t('Created data'),
          'save' => TRUE,
        ),
      ),
      'group' => t('Data'),
      'base' => 'rules_action_data_create',
    );
  }
  return $return;
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
