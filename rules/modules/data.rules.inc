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

  if (rules_data_action_type_options('action', 'data_fetch')) {
    $return['data_fetch'] = array(
      'label' => t('Fetch data'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Data type'),
          'options list' => 'rules_data_action_type_options',
          'description' => t('Specify the type of the data that should be fetched.'),
         ),
         // Further needed parameter depends on the type.
      ),
      'provides' => array(
        'data_fetched' => array('type' => 'unknown', 'label' => t('Fetched data')),
      ),
      'group' => t('Data'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_data_fetch',
      'callbacks' => array('access' => 'rules_action_data_createfetch_access'),
    );
  }

  if (rules_data_action_type_options('action', 'data_create')) {
    $return['data_create'] = array(
      'label' => t('Create new data'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Data type'),
          'options list' => 'rules_data_action_type_options',
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
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_data_create',
      'callbacks' => array('access' => 'rules_action_data_createfetch_access'),
    );
  }

  // Get an array of types that are savable.
  $cache = rules_get_cache();
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
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_data_save',
      'callbacks' => array(
        'validate' => 'rules_data_parameter_validation',
        'access' => 'rules_action_data_savedelete_access',
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
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_data_delete',
      'callbacks' => array(
        'validate' => 'rules_data_parameter_validation',
        'access' => 'rules_action_data_savedelete_access',
      ),
    );
  }
  return $return;
}

/**
 * Customize access check for data set action.
 */
function rules_action_data_set_access(RulesAbstractPlugin $element) {
  if (!RulesAbstractPluginDefaults::access($element)) {
    return FALSE;
  }
  if (isset($element->settings['data:select']) && $wrapper = $element->call('applyDataSelector', array($element->settings['data:select']))) {
    return $wrapper->access('edit');
  }
}

/**
 * Custom access callback for data create and fetch action.
 */
function rules_action_data_createfetch_access(RulesAbstractPlugin $element) {
  if (!RulesAbstractPluginDefaults::access($element)) {
    return FALSE;
  }
  $data = rules_wrap_data($data = NULL, array('type' => $element->settings['type']));
  $op = $element->getElementName() == 'data_create' ? 'create' : 'view';
  return !$data->facesAs('RulesDataAccessInterface') || $data->access($op);
}

/**
 * Custom validate callback for data fetch action.
 */
function rules_action_data_fetch_validate($element) {
  if (!isset($element->settings['type'])) {
    throw new RulesException('Invalid type specified.');
  }
}

/**
 * Custom process callback for data fetch action.
 */
function rules_action_data_fetch_process(RulesAbstractPlugin $element) {
  $data = rules_wrap_data($data = NULL, array('type' => $element->settings['type']));
  // Add the data type's needed parameter for loading to the parameter info.
  foreach ($data->loadParameter() as $name => $param_info) {
    // Prefix parameter names to avoid name clashes with existing parameters.
    $info['parameter']['param_' . $name] = $param_info;
  }
  $info['provides']['data_fetched']['type'] = $element->settings['type'];
  $element->alterInfo($info);
}

/**
 * Custom validate callback for data create action.
 */
function rules_action_data_create_validate($element) {
  if (!isset($element->settings['type'])) {
    throw new RulesException('Invalid type specified.');
  }
}

/**
 * Custom process callback for data create action.
 */
function rules_action_data_create_process(RulesAbstractPlugin $element) {
  $data = rules_wrap_data($data = NULL, array('type' => $element->settings['type']));
  // Add the data type's needed parameter for loading to the parameter info.
  foreach ($data->creationParameter() as $name => $param_info) {
    // Prefix parameter names to avoid name clashes with existing parameters.
    $info['parameter']['param_' . $name] = $param_info;
  }
  $info['provides']['data_created']['type'] = $element->settings['type'];
  $element->alterInfo($info);
}

/**
 * Custom access callback for data save and delete action.
 */
function rules_action_data_savedelete_access(RulesAbstractPlugin $element) {
  if (!RulesAbstractPluginDefaults::access($element)) {
    return FALSE;
  }
  if ($wrapper = $element->call('applyDataSelector', array($element->settings['data:select']))) {
    $data = rules_wrap_data($data = NULL, array('type' => $wrapper->type()));
    if ($data instanceof RulesDataWrapper) {
      $op = $element->getElementName() == 'data_save' ? 'save' : 'delete';
      return !$data->facesAs('RulesDataAccessInterface') || $data->access($op);
    }
  }
  return FALSE;
}

/**
 * Custom validation callback ensuring the 'data' argument is a data wrapper.
 */
function rules_data_parameter_validation(RulesAbstractPlugin $element) {
  if (!isset($element->settings['data:select'])) {
    throw new RulesException('The data argument has to be specified using data selectors.');
  }
  elseif ($element->getElementName() == 'data_set' && count(explode(':', $element->settings['data:select'], 2)) != 2) {
    // Modifying rules variables itself doesn't make any sense.
    throw new RulesException('Variables may not be changed, only their properties can be modified.');
  }
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
 * Custom process callback for the data_is condition.
 *
 * If we check the bundle property of a variable, add an assertion so that later
 * evaluated elements can make use of this information.
 */
function rules_condition_data_is_process(RulesAbstractPlugin $element) {
  if (($parts = explode(':', $element->settings['data:select'])) && count($parts) == 2 && !is_array($element->settings['value'])) {
    if (($vars = $element->availableVariables()) && isset($vars[$parts[0]])) {
      $type = $vars[$parts[0]]['type'];
      $info = entity_get_info();
      if (isset($info[$type]['object keys']['bundle']) && $info[$type]['object keys']['bundle'] == $parts[1]) {
        // Assert that the object is of bundle $value.
        $element->settings['#info']['asserts'][$parts[0]] = $element->settings['value'];
      }
    }
  }
}


/**
 * @}
 */
