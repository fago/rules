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
  $return = array(
    'text' => array(
      'label' => t('text'),
    ),
   'decimal' => array(
      'label' => t('decimal number'),
      'parent' => 'text',
    ),
   'integer' => array(
      'label' => t('integer'),
      'class' => 'RulesIntegerWrapper',
      'parent' => 'decimal',
    ),
   'date' => array(
      'label' => t('date'),
    ),
   'duration' => array(
      'label' => t('duration'),
    ),
   'boolean' => array(
      'label' => t('truth value'),
    ),
   'uri' => array(
      'label' => t('URI'),
      'parent' => 'text',
    ),
   'list' => array(
      'label' => t('list'),
      'wrap' => TRUE,
    ),
   'entity' => array(
      'label' => t('any entity'),
    )
  );
  foreach (entity_get_info() as $type => $info) {
    $return[$type] = array(
      'label' => $info['label'],
      'parent' => 'entity',
      'wrap' => TRUE,
    );
  }
  return $return;
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
        'restriction' => 'selector',
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

  if (rules_data_action_type_options('action', 'entity_fetch')) {
    $return['entity_fetch'] = array(
      'label' => t('Fetch entity by id'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Data type'),
          'options list' => 'rules_data_action_type_options',
          'description' => t('Specify the type of the data that should be fetched.'),
          'restriction' => 'input',
         ),
         'id' => array('type' => 'unknown', 'label' => t('Identifier')),
      ),
      'provides' => array(
        'entity_fetched' => array('type' => 'unknown', 'label' => t('Fetched entity')),
      ),
      'group' => t('Data'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_entity_fetch',
      'callbacks' => array('access' => 'rules_action_entity_createfetch_access'),
    );
    $return['entity_query'] = array(
      'label' => t('Fetch entity by property'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Data type'),
          'options list' => 'rules_data_action_type_options',
          'description' => t('Specify the type of the data that should be fetched.'),
          'restriction' => 'input',
         ),
        'property' => array(
          'type' => 'text',
          'label' => t('Property'),
          'description' => t('The property by which the data is to be selected.'),
          'restriction' => 'input',
         ),
        'value' => array(
          'type' => 'unknown',
          'label' => t('Value'),
          'description' => t('The property value of the data to be fetched.'),
         ),
         'limit' => array(
           'type' => 'integer',
           'label' => t('Limit result count'),
           'description' => t('Limit the maximum number of fetched data items.'),
           'optional' => TRUE,
           'default value' => '10',
         ),
      ),
      'provides' => array(
        'entity_fetched' => array('type' => 'list', 'label' => t('Fetched entity')),
      ),
      'group' => t('Data'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_entity_query',
    );
  }

  if (rules_data_action_type_options('action', 'entity_create')) {
    $return['entity_create'] = array(
      'label' => t('Create a new entity'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Data type'),
          'options list' => 'rules_data_action_type_options',
          'description' => t('Specify the type of the data that should be created.'),
          'restriction' => 'input',
         ),
         // Further needed parameter depends on the type.
      ),
      'provides' => array(
        'entity_created' => array(
          'type' => 'unknown',
          'label' => t('Created data'),
          'save' => TRUE,
        ),
      ),
      'group' => t('Data'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_entity_create',
      'callbacks' => array('access' => 'rules_action_entity_createfetch_access'),
    );
  }

  // Get an array of types that are savable.
  if ($types = array_keys(rules_entity_type_options('save callback'))) {
    $return['entity_save'] = array(
      'label' => t('Save entity'),
      'parameter' => array(
        'data' => array(
          'type' => $types,
          'label' => t('Data to save'),
          'description' => t('The data, which should be saved permanently.'),
          'restriction' => 'selector',
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
      'base' => 'rules_action_entity_save',
      'callbacks' => array(
        'validate' => 'rules_data_parameter_validation',
        'access' => 'rules_action_entity_savedelete_access',
      ),
    );
  }

  // Get an array of types that are deletable.
  if ($types = array_keys(rules_entity_type_options('deletion callback'))) {
    $return['entity_delete'] = array(
      'label' => t('Delete entity'),
      'parameter' => array(
        'data' => array(
          'type' => $types,
          'label' => t('Data to delete'),
          'description' => t('The data, which should be deleted permanently.'),
          'restriction' => 'selector',
         ),
      ),
      'group' => t('Data'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_entity_delete',
      'callbacks' => array(
        'validate' => 'rules_data_parameter_validation',
        'access' => 'rules_action_entity_savedelete_access',
      ),
    );
  }
  $return['list_add'] = array(
    'label' => t('Add list item'),
    'parameter' => array(
      'list' => array(
        'type' => 'list',
        'label' => t('List'),
        'description' => t('The data list, to which an item is to be added.'),
        'restriction' => 'selector',
       ),
      'item' => array(
        'type' => 'unknown',
        'label' => t('Item to add'),
       ),
       'pos' => array(
         'type' => 'text',
         'label' => t('Insert position'),
         'optional' => TRUE,
         'default value' => 'end',
         'options list' => 'rules_action_data_list_add_positions',
       ),
    ),
    'group' => t('Data'),
    'base' => 'rules_action_data_list_add',
    'callbacks' => array(
      'validate' => 'rules_data_validate_list_item_type',
    ),
  );
  $return['list_remove'] = array(
    'label' => t('Remove a list item'),
    'parameter' => array(
      'list' => array(
        'type' => 'list',
        'label' => t('List'),
        'description' => t('The data list for which an item is to be removed.'),
        'restriction' => 'selector',
       ),
      'item' => array(
        'type' => 'unknown',
        'label' => t('Item to remove'),
       ),
    ),
    'group' => t('Data'),
    'base' => 'rules_action_data_list_remove',
    'callbacks' => array(
      'validate' => 'rules_data_validate_list_item_type',
    ),
  );
  return $return;
}

/**
 * Customize access check for data set action.
 */
function rules_action_data_set_access(RulesAbstractPlugin $element) {
  if (!$element->extender('RulesAbstractPluginDefaults')->access()) {
    return FALSE;
  }
  if (isset($element->settings['data:select']) && $wrapper = $element->call('applyDataSelector', array($element->settings['data:select']))) {
    return $wrapper instanceof EntityMetadataWrapper && $wrapper->access('edit');
  }
}

/**
 * Custom access callback for data create and fetch action.
 */
function rules_action_entity_createfetch_access(RulesAbstractPlugin $element) {
  if (!$element->extender('RulesAbstractPluginDefaults')->access()) {
    return FALSE;
  }
  $op = $element->getElementName() == 'entity_create' ? 'create' : 'view';
  return entity_metadata_entity_access($op, $element->settings['type']);
}

/**
 * Custom validate callback for data fetch action.
 */
function rules_action_entity_fetch_validate($element) {
  if (!isset($element->settings['type'])) {
    throw new RulesException('Invalid type specified.');
  }
}

/**
 * Custom process callback for data fetch action.
 */
function rules_action_entity_fetch_process(RulesAbstractPlugin $element) {
  $info = entity_get_info($element->settings['type']);

  // Fix the type of the identifier.
  $element_info['parameter']['id']['type'] = isset($info['object keys']['name']) ? 'text' : 'integer';

  // Add an optional revision parameter, if supported.
  if (isset($info['object keys']['revision'])) {
    $element_info['parameter']['revision_id'] = array(
      'type' => 'integer',
      'label' => t('Revision identifier'),
      'optional' => TRUE,
    );
  }
  $element_info['provides']['entity_fetched']['type'] = $element->settings['type'];
  $element->alterInfo($element_info);
}

/**
 * Custom validate callback for data query action.
 */
function rules_action_entity_query_validate($element) {
  if (!isset($element->settings['type'])) {
    throw new RulesException('Invalid type specified.');
  }
  if (!isset($element->settings['property'])) {
    throw new RulesException('Invalid property specified.');
  }
}

/**
 * Custom access callback for the data query action.
 */
function rules_action_entity_query_access(RulesAbstractPlugin $element) {
  if (!rules_action_entity_createfetch_access($element)) {
    return FALSE;
  }
  $properties = entity_metadata_get_properties($element->settings['type']);
  if (isset($properties[$element->settings['property']]['access callback'])) {
    return call_user_func($properties[$element->settings['property']]['access callback'], 'view', $element->settings['property'], $element->settings['type'], NULL, NULL);
  }
  return TRUE;
}

/**
 * Custom callback to process the data query action.
 */
function rules_action_entity_query_process(RulesAbstractPlugin $element) {
  $info['parameter']['value']['type'] = $element->settings['type'];
  $info['provides']['entity_fetched']['type'] = 'list<' . $element->settings['type'] . '>';
  $element->alterInfo($info);
}

/**
 * Custom validate callback for data create action.
 */
function rules_action_entity_create_validate($element) {
  if (!isset($element->settings['type'])) {
    throw new RulesException('Invalid type specified.');
  }
}

/**
 * Custom process callback for data create action.
 */
function rules_action_entity_create_process(RulesAbstractPlugin $element) {
  $wrapper = entity_metadata_wrapper($element->settings['type']);
  // Add the data type's needed parameter for loading to the parameter info.
  foreach ($wrapper as $name => $child) {
    $info = $child->info();
    if (!empty($info['required'])) {
      $info += array('type' => 'text');
      // Prefix parameter names to avoid name clashes with existing parameters.
      $element_info['parameter']['param_' . $name] = array('type' => $info['type'], 'label' => $info['label']);
    }
  }
  $element_info['provides']['entity_created']['type'] = $element->settings['type'];
  $element->alterInfo($element_info);
}

/**
 * Custom access callback for data save and delete action.
 */
function rules_action_entity_savedelete_access(RulesAbstractPlugin $element) {
  if (!$element->extender('RulesAbstractPluginDefaults')->access()) {
    return FALSE;
  }
  if ($wrapper = $element->call('applyDataSelector', array($element->settings['data:select']))) {
    $op = $element->getElementName() == 'entity_save' ? 'save' : 'delete';
    return $wrapper instanceof EntityDrupalWrapper && $wrapper->entityAccess($op);
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
 * Options list callback for data actions.
 *
 * @param $type
 *   The element type, e.g. 'action' or 'condition.
 * @param $name
 *   The name of the element to return options for.
 */
function rules_data_action_type_options($type, $name) {
  $key = $name == 'entity_fetch' ? 'base table' : 'creation callback';
  return rules_entity_type_options($key);
}

/**
 * Returns options containing entity types having the given key set in the info.
 */
function rules_entity_type_options($key) {
  $info = entity_get_info();
  $types = array();
  foreach ($info as $type => $entity_info) {
    if (!empty($entity_info[$key])) {
      $types[$type] = $entity_info['label'];
    }
  }
  return $types;
}

/**
 * Validation callback to ensure the list item matches to the list.
 */
function rules_data_validate_list_item_type(RulesAbstractPlugin $element) {
  $list_info = $this->getArgumentInfo('list');
  $list_info['type'] = isset($list_info['type']) ? entity_metadata_list_extract_type($list_info['type']) : FALSE;
  if (!empty($list_info['type']) && ($item_info = $this->getArgumentInfo('item'))) {
    if (!RulesData::typesMatch($item_info, $list_info)) {
      throw new RulesException('The item specified is of an invalid type.');
    }
  }
}

/**
 * Options list callback for possible insertion positions.
 */
function rules_action_data_list_add_positions() {
  return array(
    'end' => t('Append the item to the end.'),
    'start' => t('Prepend the item to the front.'),
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
 * Custom process callback for the data_is condition.
 *
 * If we check the bundle property of a variable, add an assertion so that later
 * evaluated elements can make use of this information.
 */
function rules_condition_data_is_process(RulesAbstractPlugin $element) {
  if (($parts = explode(':', $element->settings['data:select'])) && count($parts) == 2 && isset($element->settings['value']) && !is_array($element->settings['value'])) {
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
