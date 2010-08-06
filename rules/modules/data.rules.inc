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
 * Implements hook_rules_action_info() on behalf of the pseudo data module.
 * @see rules_core_modules()
 */
function rules_data_action_info() {
  $return['data_set'] = array(
    'label' => t('Modify data'),
    'parameter' => array(
      'data' => array(
        'type' => '*',
        'label' => t('Data'),
        'description' => t('Specifies the data to be modified using a data selector, e.g. "node:author:name".'),
        'restriction' => 'selector',
       ),
      'value' => array(
        'type' => '*',
        'label' => t('Value'),
        'description' => t('The new value to set for the specified data.'),
      ),
    ),
    'group' => t('Data'),
    'base' => 'rules_action_data_set',
  );

  if (rules_data_action_type_options(NULL, 'entity_fetch')) {
    $return['entity_fetch'] = array(
      'label' => t('Fetch entity by id'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Entity type'),
          'options list' => 'rules_data_action_type_options',
          'description' => t('Specifies the type of entity that should be fetched.'),
          'restriction' => 'input',
         ),
         'id' => array('type' => 'unknown', 'label' => t('Identifier')),
      ),
      'provides' => array(
        'entity_fetched' => array('type' => 'unknown', 'label' => t('Fetched entity')),
      ),
      'group' => t('Entities'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_entity_fetch',
      'callbacks' => array(
        'access' => 'rules_action_entity_createfetch_access',
        'form_alter' => 'rules_action_type_form_alter',
      ),
    );
    $return['entity_query'] = array(
      'label' => t('Fetch entity by property'),
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Entity type'),
          'options list' => 'rules_data_action_type_options',
          'description' => t('Specifies the type of the entity that should be fetched.'),
          'restriction' => 'input',
         ),
        'property' => array(
          'type' => 'text',
          'label' => t('Property'),
          'description' => t('The property by which the entity is to be selected.'),
          'restriction' => 'input',
         ),
        'value' => array(
          'type' => 'unknown',
          'label' => t('Value'),
          'description' => t('The property value of the entity to be fetched.'),
         ),
         'limit' => array(
           'type' => 'integer',
           'label' => t('Limit result count'),
           'description' => t('Limit the maximum number of fetched entities.'),
           'optional' => TRUE,
           'default value' => '10',
         ),
      ),
      'provides' => array(
        'entity_fetched' => array('type' => 'list', 'label' => t('Fetched entity')),
      ),
      'group' => t('Entities'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_entity_query',
      'callbacks' => array(
        'form_alter' => 'rules_action_type_form_alter',
      ),
    );
  }

  if (rules_data_action_type_options(NULL, 'entity_create')) {
    $return['entity_create'] = array(
      'label' => t('Create a new entity'),
      'named parameter' => TRUE,
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Entity type'),
          'options list' => 'rules_data_action_type_options',
          'description' => t('Specifies the type of the entity that should be created.'),
          'restriction' => 'input',
         ),
         // Further needed parameter depends on the type.
      ),
      'provides' => array(
        'entity_created' => array(
          'type' => 'unknown',
          'label' => t('Created entity'),
          'save' => TRUE,
        ),
      ),
      'group' => t('Entities'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_entity_create',
      'callbacks' => array(
        'access' => 'rules_action_entity_createfetch_access',
        'form_alter' => 'rules_action_type_form_alter',
        'validate' => 'rules_action_create_type_validate',
      ),
    );
  }

  // Get an array of types that are savable.
  if ($types = array_keys(rules_entity_type_options('save callback'))) {
    $return['entity_save'] = array(
      'label' => t('Save entity'),
      'parameter' => array(
        'data' => array(
          'type' => $types,
          'label' => t('Entity'),
          'description' => t('Specifies the entity, which should be saved permanently.'),
          'restriction' => 'selector',
         ),
         'immediate' => array(
           'type' => 'boolean',
           'label' => t('Force saving immediately'),
           'description' => t('Usually saving is postponed till the end of the evaluation, so that multiple saves can be fold into one. If this set, saving is forced to happen immediately.'),
           'default value' => FALSE,
           'optional' => TRUE,
           'restriction' => 'input',
         ),
      ),
      'group' => t('Entities'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_entity_save',
      'callbacks' => array(
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
          'label' => t('Entity'),
          'description' => t('Specifies the entity, which should be deleted permanently.'),
          'restriction' => 'selector',
         ),
      ),
      'group' => t('Entities'),
      'access callback' => 'rules_data_action_access',
      'base' => 'rules_action_entity_delete',
      'callbacks' => array(
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
  $return['variable_add'] = array(
    'label' => t('Add a variable'),
    'named parameter' => TRUE,
    'parameter' => array(
      'type' => array(
        'type' => 'text',
        'label' => t('Type'),
        'options list' => 'rules_data_action_variable_add_options',
        'description' => t('Specifies the type of the variable that should be added.'),
        'restriction' => 'input',
      ),
      'value' => array(
        'type' => 'unknown',
        'label' => t('Value'),
      ),
    ),
    'provides' => array(
      'variable_added' => array(
        'type' => 'unknown',
        'label' => t('Added variable'),
      ),
    ),
    'group' => t('Data'),
    'base' => 'rules_action_variable_add',
    'callbacks' => array(
      'form_alter' => 'rules_action_type_form_alter',
      'validate' => 'rules_action_create_type_validate',
    ),
  );

  if (rules_data_action_data_create_options()) {
    $return['data_create'] = array(
      'label' => t('Create a data structure'),
      'named parameter' => TRUE,
      'parameter' => array(
        'type' => array(
          'type' => 'text',
          'label' => t('Type'),
          'options list' => 'rules_data_action_data_create_options',
          'description' => t('Specifies the type of the data structure that should be created.'),
          'restriction' => 'input',
         ),
         // Further needed parameters depend on the type.
      ),
      'provides' => array(
        'data_created' => array(
          'type' => 'unknown',
          'label' => t('Created data'),
        ),
      ),
      'group' => t('Data'),
      'base' => 'rules_action_data_create',
      'callbacks' => array(
        'form_alter' => 'rules_action_type_form_alter',
        'validate' => 'rules_action_create_type_validate',
      ),
    );
  }
  return $return;
}

/**
 * Customize access check for data set action.
 */
function rules_action_data_set_access(RulesAbstractPlugin $element) {
  if (isset($element->settings['data:select']) && $wrapper = $element->applyDataSelector($element->settings['data:select'])) {
    return $wrapper instanceof EntityMetadataWrapper && $wrapper->access('edit');
  }
}

/**
 * Custom validation callback for the data set action.
 */
function rules_action_data_set_validate(RulesAbstractPlugin $element) {
  $element->settings += array('data:select' => NULL);
  if (count(explode(':', $element->settings['data:select'], 2)) != 2) {
    // Modifying rules variables itself doesn't make any sense.
    throw new RulesException('Variables may not be changed, only their properties can be modified.', array(), array($element, 'parameter', 'data'));
  }
  $info = $element->applyDataSelector($element->settings['data:select'])->info();
  if (empty($info['setter callback'])) {
    // Modifying rules variables itself doesn't make any sense.
    throw new RulesException("The selected data property doesn't support writing.", array(), array($element, 'parameter', 'data'));
  }
}

/**
 * Form alter callback for the data_set action.
 */
function rules_action_data_set_form_alter(&$form, &$form_state, $options, RulesAbstractPlugin $element) {
  if (!empty($options['init']) && !isset($form_state['rules_element_step'])) {
    $form['negate']['#access'] = FALSE;
    unset($form['parameter']['value']);
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Continue'),
      '#limit_validation_errors' => array('parameter', 'data'),
      '#submit' => array('rules_form_submit_rebuild'),
    );
    $form_state['rules_element_step'] = 'data_value';
    // Clear the parameter mode for the value parameter, so its gets the proper
    // default value based upon the type of the the selected data on rebuild.
    unset($form_state['parameter_mode']['value']);
  }
  else {
    // Change the data parameter to be not editable.
    $form['parameter']['data']['settings']['#access'] = FALSE;
    // TODO: improve display
    $form['parameter']['data']['info'] = array(
      '#prefix' => '<p>',
      '#markup' => t('<strong>Selected data:</strong> %selector', array('%selector' => $element->settings['data:select'])),
      '#suffix' => '</p>',
    );
  }
}

/**
 * Process callback for the data_set action.
 */
function rules_action_data_set_process(RulesAbstractPlugin $element) {
  $element->settings += array('data:select' => NULL);
  if ($wrapper = $element->applyDataSelector($element->settings['data:select'])) {
    $element_info['parameter']['value']['type'] = $wrapper->type();
    $element_info['parameter']['value']['options list']  = $wrapper->optionsList() ? 'rules_data_selector_options_list' : FALSE;
    $element->alterInfo($element_info);
  }
}

/**
 * Custom access callback for data create and fetch action.
 */
function rules_action_entity_createfetch_access(RulesAbstractPlugin $element) {
  $op = $element->getElementName() == 'entity_create' ? 'create' : 'view';
  return entity_metadata_entity_access($op, $element->settings['type']);
}

/**
 * Custom validate callback for data fetch action.
 */
function rules_action_entity_fetch_validate($element) {
  if (!isset($element->settings['type'])) {
    throw new RulesException('Invalid type specified.', array(), array($element, 'parameter', 'type'));
  }
}

/**
 * Custom process callback for data fetch action.
 */
function rules_action_entity_fetch_process(RulesAbstractPlugin $element) {
  $element->settings += array('type' => NULL);
  $info = entity_get_info($element->settings['type']);

  // Fix the type of the identifier.
  $element_info['parameter']['id']['type'] = isset($info['entity keys']['name']) ? 'text' : 'integer';

  // Add an optional revision parameter, if supported.
  if (!empty($info['entity keys']['revision'])) {
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
    throw new RulesException('Invalid type specified.', array(), array($element, 'parameter', 'type'));
  }
  if (!isset($element->settings['property'])) {
    throw new RulesException('Invalid property specified.', array(), array($element, 'parameter', 'property'));
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
  if (isset($element->settings['property']) && isset($properties[$element->settings['property']]['access callback'])) {
    return call_user_func($properties[$element->settings['property']]['access callback'], 'view', $element->settings['property'], $element->settings['type'], NULL, NULL);
  }
  return TRUE;
}

/**
 * Custom callback to process the data query action.
 */
function rules_action_entity_query_process(RulesAbstractPlugin $element) {
  $element->settings += array('type' => NULL);
  $info['parameter']['value']['type'] = $element->settings['type'];
  $info['provides']['entity_fetched']['type'] = 'list<' . $element->settings['type'] . '>';
  $element->alterInfo($info);
}

/**
 * Custom validate callback for entity create, add variable and data create
 * action.
 */
function rules_action_create_type_validate($element) {
  if (!isset($element->settings['type'])) {
    throw new RulesException('Invalid type specified.', array(), array($element, 'parameter', 'type'));
  }
}

/**
 * Custom process callback for entity create action.
 */
function rules_action_entity_create_process(RulesAbstractPlugin $element) {
  $element->settings += array('type' => NULL);
  $wrapper = entity_metadata_wrapper($element->settings['type']);
  // Add the data type's needed parameter for loading to the parameter info.
  foreach ($wrapper as $name => $child) {
    $info = $child->info();
    if (!empty($info['required'])) {
      $info += array('type' => 'text');
      // Prefix parameter names to avoid name clashes with existing parameters.
      // @TODO: This saves translated strings. TODO: fix.
      $element_info['parameter']['param_' . $name] = array_intersect_key($info, array_flip(array('type', 'label', 'description')));
      $element_info['parameter']['param_' . $name]['options list']  = $child->optionsList() ? 'rules_action_entity_parameter_options_list' : FALSE;
    }
  }
  $element_info['provides']['entity_created']['type'] = $element->settings['type'];
  if (($bundleKey = $wrapper->entityKey('bundle')) && isset($element->settings['param_' . $bundleKey])) {
    $element_info['provides']['entity_created']['bundle'] = $element->settings['param_' . $bundleKey];
  }
  $element->alterInfo($element_info);
}

/**
 * Options list callback for a parameter of entity_create.
 */
function rules_action_entity_parameter_options_list(RulesPlugin $element, $param_name) {
  // Remove the parameter name prefix 'param_'.
  $property_name = substr($param_name, 6);
  $wrapper = entity_metadata_wrapper($element->settings['type']);
  // The possible values of the "value" parameter are those of the data param.
  return $wrapper->$property_name->optionsList();
}

/**
 * Form alter callback for actions relying on the entity type or the data type.
 */
function rules_action_type_form_alter(&$form, &$form_state, $options, RulesAbstractPlugin $element) {
  $first_step = empty($element->settings['type']);
  $form['reload'] = array(
    '#weight' => 5,
    '#type' => 'submit',
    '#name' => 'reload',
    '#value' => $first_step ? t('Continue') : t('Reload form'),
    '#limit_validation_errors' => array('parameter', 'type'),
    '#submit' => array('rules_action_type_form_submit_rebuild'),
    '#ajax' => rules_ui_form_default_ajax(),
  );
  // Use ajax and trigger as the reload button.
  $form['parameter']['type']['settings']['type']['#ajax'] = $form['reload']['#ajax'] + array(
    'event' => 'change',
    'trigger_as' => array('name' => 'reload'),
  );

  if ($first_step) {
    // In the first step show only the type select.
    foreach (element_children($form['parameter']) as $key) {
      if ($key != 'type') {
        unset($form['parameter'][$key]);
      }
    }
    unset($form['submit']);
    unset($form['provides']);
    // Disable #ajax for the first step as it has troubles with lazy-loaded JS.
    // @todo: Re-enable once JS lazy-loading is fixed in core.
    unset($form['parameter']['type']['settings']['type']['#ajax']);
    unset($form['reload']['#ajax']);
  }
  else {
    // Hide the reload button in case js is enabled and it's not the first step.
    $form['reload']['#attributes'] = array('class' => array('rules-hide-js'));
  }
}

/**
 * FAPI submit callback for reloading the type form for entities or data types.
 */
function rules_action_type_form_submit_rebuild($form, &$form_state) {
  rules_form_submit_rebuild($form, $form_state);
  // Clear the parameter modes for the parameters, so they get the proper
  // default values based upon the data types on rebuild.
  $form_state['parameter_mode'] = array();
}


/**
 * Custom access callback for data save and delete action.
 */
function rules_action_entity_savedelete_access(RulesAbstractPlugin $element) {
  if ($wrapper = $element->applyDataSelector($element->settings['data:select'])) {
    $op = $element->getElementName() == 'entity_save' ? 'save' : 'delete';
    return $wrapper instanceof EntityDrupalWrapper && $wrapper->entityAccess($op);
  }
  return FALSE;
}

/**
 * Options list callback for data actions.
 *
 * @param $name
 *   The name of the element to return options for.
 */
function rules_data_action_type_options(RulesAbstractPlugin $element = NULL, $name = NULL) {
  $name = isset($element) ? $element->getElementName() : $name;
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
  $list_info = $element->getArgumentInfo('list');
  $list_info['type'] = isset($list_info['type']) ? entity_metadata_list_extract_type($list_info['type']) : FALSE;
  if (!empty($list_info['type']) && ($item_info = $element->getArgumentInfo('item'))) {
    if (!RulesData::typesMatch($item_info, $list_info)) {
      throw new RulesException('The item specified is of an invalid type.', array(), array($element, 'parameter', 'item'));
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
 * Options list callback for variable add action.
 */
function rules_data_action_variable_add_options() {
  return RulesPluginUI::getOptions('data_info');
}

/**
 * Custom process callback for variable add action.
 */
function rules_action_variable_add_process(RulesAbstractPlugin $element) {
  $type = $element->settings['type'];
  $cache = rules_get_cache();
  $type_info = $cache['data_info'][$type];
  $element_info['parameter']['value']['type'] = $type;
  $element_info['parameter']['value']['label'] = $type_info['label'];
  $element_info['provides']['variable_added']['type'] = $type;
  $element->alterInfo($element_info);
}

/**
 * Options list callback for data create action.
 */
function rules_data_action_data_create_options() {
  $cache = rules_get_cache();
  $data_info = $cache['data_info'];
  $entity_info = entity_get_info();
  // Remove entities.
  $data_info = array_diff_key($data_info, $entity_info);
  $options = array();
  foreach ($data_info as $type => $properties) {
    if (isset($properties['creation callback'])) {
      // Add data types with creation callback only.
      $options[$type] = $properties['label'];
    }
  }
  natcasesort($options);
  return $options;
}

/**
 * Custom process callback for data create action.
 */
function rules_action_data_create_process(RulesAbstractPlugin $element) {
  $type = $element->settings['type'];
  $cache = rules_get_cache();
  $type_info = $cache['data_info'][$type];
  if (isset($type_info['property info'])) {
    // Add the data type's properties as parameters.
    foreach ($type_info['property info'] as $property => $property_info) {
      // Prefix parameter names to avoid name clashes with existing parameters.
      $element_info['parameter']['param_' . $property] = array_intersect_key($property_info, array_flip(array('type', 'label')));
      if (empty($property_info['required'])) {
        $element_info['parameter']['param_' . $property]['optional'] = TRUE;
      }
    }
  }
  $element_info['provides']['data_created']['type'] = $type;
  $element->alterInfo($element_info);
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
          'description' => t('The data to be compared, specified by using a data selector, e.g. "node:author:name".'),
         ),
        'op' => array(
          'type' => 'text',
          'label' => t('Operator'),
          'description' => t('The comparison operator.'),
          'optional' => TRUE,
          'default value' => '==',
          'options list' => 'rules_condition_data_is_operator_options',
          'restriction' => 'input',
        ),
        'value' => array(
          'type' => '*',
          'label' => t('Data value'),
          'description' => t('The value to compare the data with.'),
        ),
      ),
      'group' => t('Data'),
      'base' => 'rules_condition_data_is',
    ),
    'entity_is_new' => array(
      'label' => t('Entity is new'),
      'parameter' => array(
        'entity' => array(
          'type' => 'entity',
          'label' => t('Entity'),
          'description' => t('Specifies the entity for which to evaluate the condition.'),
          'restriction' => 'selector',
        ),
      ),
      'group' => t('Entities'),
      'base' => 'rules_condition_entity_is_new',
    ),
    'entity_has_field' => array(
      'label' => t('Entity has field'),
      'parameter' => array(
        'entity' => array(
          'type' => 'entity',
          'label' => t('Entity'),
          'description' => t('Specifies the entity for which to evaluate the condition.'),
          'restriction' => 'selector',
        ),
        'field' => array(
          'type' => 'text',
          'label' => t('Field'),
          'description' => t('The name of the field to check for.'),
          'options list' => 'rules_condition_entity_has_field_options',
          'restriction' => 'input',
        ),
      ),
      'group' => t('Entities'),
      'base' => 'rules_condition_entity_has_field',
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
  // Clear out any old alterions.
  unset($element->settings['#info']);
  $element->settings += array('data:select' => NULL);

  if ($wrapper = $element->applyDataSelector($element->settings['data:select'])) {
    $element_info['parameter']['value']['type'] = $wrapper->type();
    $element_info['parameter']['value']['options list']  = $wrapper->optionsList() ? 'rules_data_selector_options_list' : FALSE;
    $element->alterInfo($element_info);
  }
}

/**
 * If the bundle is compared, add the metadata assertion so other elements
 * can make use of properties specific to the bundle.
 */
function rules_condition_data_is_assertions($element) {
  // Assert the bundle of entities, if its compared.
  if ($wrapper = $element->applyDataSelector($element->settings['data:select'])) {
    $info = $wrapper->info();
    if (isset($info['parent']) && $info['parent'] instanceof EntityDrupalWrapper) {
      $entity_info = $info['parent']->entityInfo();
      if (isset($entity_info['entity keys']['bundle']) && $entity_info['entity keys']['bundle'] == $info['name']) {
        // Assert that the entity is of bundle $value.
        $value = is_array($element->settings['value']) ? $element->settings['value'] : array($element->settings['value']);
        // Chop of the last part of the selector.
        $parts = explode(':', $element->settings['data:select'], -1);
        return array(implode(':', $parts) => array('bundle' => $value));
      }
    }
  }
}

/**
 * Form alter callback for the condition data_is.
 * Use multiple steps to configure the condition as the needed type of the value
 * depends on the selected data.
 */
function rules_condition_data_is_form_alter(&$form, &$form_state, $options, RulesAbstractPlugin $element) {
  if (!empty($options['init']) && !isset($form_state['rules_element_step'])) {
    unset($form['parameter']['op'], $form['parameter']['value']);
    $form['negate']['#access'] = FALSE;
    $form_state['rules_element_step'] = 'data_value';
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Continue'),
      '#limit_validation_errors' => array('parameter', 'data'),
      '#submit' => array('rules_form_submit_rebuild'),
    );
    // Clear the parameter mode for the value parameter, so its gets the proper
    // default value based upon the type of the the selected data on rebuild.
    unset($form_state['parameter_mode']['value']);
  }
  else {
    // Change the data parameter to be not editable.
    $form['parameter']['data']['settings']['#access'] = FALSE;
    // TODO: improve display
    $form['parameter']['data']['info'] = array(
      '#prefix' => '<p>',
      '#markup' => t('<strong>Selected data:</strong> %selector', array('%selector' => $element->settings['data:select'])),
      '#suffix' => '</p>',
    );

    // Limit the operations to what makes sense for the data type.
    $info = $element->pluginParameterInfo();
    if (!RulesData::typesMatch($info['value'], array('type' => array('decimal', 'date')))) {
      $options =& $form['parameter']['op']['settings']['op']['#options'];
      unset($options['<'], $options['>']);
    }
    if ($info['value']['type'] != 'text' || !empty($info['value']['options list'])) {
      unset($form['parameter']['op']['settings']['op']['#options']['contains']);
    }
  }
}

/**
 * Provides configuration help for the data_is condition.
 */
function rules_condition_data_is_help() {
  return array('#markup' => t('Compare two data values of the same type with each other.'));}

/**
 * Options list callback for condition data_is.
 */
function rules_condition_data_is_operator_options() {
  return array(
    '==' => t('equals'),
    '<' => t('is lower than'),
    '>' => t('is greather than'),
    'contains' => t('contains'),
  );
}

/**
 * Help callback for condition entity_is_new.
 */
function rules_condition_entity_is_new_help() {
  return t('This condition determines whether the specified entity has just been created and has not yet been saved to the database.');
}

/**
 * Returns options for choosing a field for the selected entity.
 */
function rules_condition_entity_has_field_options(RulesAbstractPlugin $element) {
  $options = array();
  foreach (field_info_fields() as $field_name => $field) {
    $options[$field_name] = $field_name;
  }
  return $options;
}

/**
 * Assert that the entity has the field, if there is metadata for the field.
 */
function rules_condition_entity_has_field_assertions($element) {
  // Assert the bundle of entities, if its compared.
  if ($wrapper = $element->applyDataSelector($element->settings['entity:select'])) {
    $type = $wrapper->type();
    $field_property = str_replace('_', '-', $element->settings['field']);
    // Get all possible properties and check whether we have one for the field.
    $properties = entity_metadata_get_properties($type == 'entity' ? NULL : $type);

    if (isset($properties[$field_property])) {
      $assertion = array('property info' => array($field_property => $properties[$field_property]));
      return array($element->settings['entity:select'] => $assertion);
    }
  }
}

/**
 * Returns the options list specified by the selected data property.
 */
function rules_data_selector_options_list(RulesAbstractPlugin $element) {
  // The possible values of the "value" parameter are those of the data param.
  if (isset($element->settings['data:select']) && $wrapper = $element->applyDataSelector($element->settings['data:select'])) {
    return $wrapper->optionsList();
  }
}

/**
 * Gets all view modes of an entity for an entity_view event.
 */
function rules_get_entity_view_modes($type, $name) {
  if ($type == 'event') {
    $entity_type = substr($name, 0, -5);
    $info = entity_get_info($entity_type);
    foreach ($info['view modes'] as $mode => $mode_info) {
      $modes[$mode] = $mode_info['label'];
    }
    return $modes;
  }
}

/**
 * @}
 */
