<?php

/**
 * @file
 * Contains rules integration for the data module needed during evaluation.
 *
 * @addtogroup rules
 * @{
 */

/**
 * Action: Modify data.
 */
function rules_action_data_set($wrapper, $value, $settings, $state, $element) {
  if ($wrapper instanceof EntityMetadataWrapper) {
    try {
      // Update the value first then save changes, if possible.
      $wrapper->set($value);
    }
    catch (EntityMetadataWrapperException $e) {
      throw new RulesException('Unable to modify data "@selector": ' . $e->getMessage(), array('@selector' => $settings['data:select']));
    }
    // Save changes if a property of a variable has been changed.
    if (strpos($element->settings['data:select'], ':') !== FALSE) {
      $info = $wrapper->info();
      // We always have to save the changes in the parent entity. E.g. when the
      // node author is changed, we don't want to save the author but the node.
      $state->saveChanges(implode(':', explode(':', $settings['data:select'], -1)), $info['parent']);
    }
  }
  else {
    // A not wrapped variable (e.g. a number) is being set. As we don't have
    // a reference on the variable, we need to directly update the variable in
    // the state.
    $var_name = $element->settings['data:select'];
    $state->variables[$var_name] = $value;
  }
}

/**
 * Info alter callback for the data_set action.
 */
function rules_action_data_set_info_alter(&$element_info, $element) {
  $element->settings += array('data:select' => NULL);
  if ($wrapper = $element->applyDataSelector($element->settings['data:select'])) {
    $element_info['parameter']['value']['type'] = $wrapper->type();
    $element_info['parameter']['value']['options list']  = $wrapper->optionsList() ? 'rules_data_selector_options_list' : FALSE;
  }
}

/**
 * Action: Fetch data.
 */
function rules_action_entity_fetch($type, $id, $revision) {
  $info = entity_get_info($type);

  // Support the revision parameter, if applicable.
  if (!empty($info['entity keys']['revision']) && isset($revision)) {
    $conditions = array($info['entity keys']['revision'] => $revision);
  }

  $return = entity_load($type, array($id), isset($conditions) ? $conditions : array());
  $entity = reset($return);
  if (!$entity) {
    throw new RulesException('Unable to load @entity with id "@id"', array('@id' => $id, '@entity' => $type));
  }
  return array('entity_fetched' => $entity);
}


/**
 * Info alteration callback for the entity fetch action.
 */
function rules_action_entity_fetch_info_alter(&$element_info, RulesAbstractPlugin $element) {
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
}

/**
 * Action: Query entities.
 */
function rules_action_entity_query($type, $property, $value, $limit) {
  $return = entity_property_query($type, $property, $value, $limit);
  return array('entity_fetched' => array_values($return));
}


/**
 * Info alteration callback for the entity query action.
 */
function rules_action_entity_query_info_alter(&$element_info, RulesAbstractPlugin $element) {
  $element->settings += array('type' => NULL, 'property' => NULL);
  if ($element->settings['type']) {
    $element_info['parameter']['property']['options list'] = 'rules_action_entity_query_property_options_list';

    if ($element->settings['property']) {
      $wrapper = entity_metadata_wrapper($element->settings['type']);
      if (isset($wrapper->{$element->settings['property']}) && $property = $wrapper->{$element->settings['property']}) {
        $element_info['parameter']['value']['type'] = $property->type();
        $element_info['parameter']['value']['options list']  = $property->optionsList() ? 'rules_action_entity_query_value_options_list' : FALSE;
      }
    }
  }
  $element_info['provides']['entity_fetched']['type'] = 'list<' . $element->settings['type'] . '>';
}

/**
 * Action: Create entities.
 */
function rules_action_entity_create($args, $element) {
  $values = array();
  foreach ($element->pluginParameterInfo() as $name => $info) {
    if ($name != 'type') {
      // Remove the parameter name prefix 'param_'.
      $values[substr($name, 6)] = $args[$name];
    }
  }
  try {
    $data = entity_property_values_create_entity($args['type'], $values);
    return array('entity_created' => $data);
  }
  catch (EntityMetadataWrapperException $e) {
    throw new RulesException('Unable to create entity @type": ' . $e->getMessage(), array('@type' => $args['type']), $element);
  }
}

/**
 * Info alteration callback for the entity create action.
 */
function rules_action_entity_create_info_alter(&$element_info, RulesAbstractPlugin $element) {
  if (!empty($element->settings['type'])) {
    $wrapper = entity_metadata_wrapper($element->settings['type']);
    // Add the data type's needed parameter for loading to the parameter info.
    foreach ($wrapper as $name => $child) {
      $info = $child->info();
      if (!empty($info['required'])) {
        $info += array('type' => 'text');
        // Prefix parameter names to avoid name clashes with existing parameters.
        $element_info['parameter']['param_' . $name] = array_intersect_key($info, array_flip(array('type', 'label', 'description')));
        $element_info['parameter']['param_' . $name]['options list']  = $child->optionsList() ? 'rules_action_entity_parameter_options_list' : FALSE;
      }
    }
    $element_info['provides']['entity_created']['type'] = $element->settings['type'];
    if (($bundleKey = $wrapper->entityKey('bundle')) && isset($element->settings['param_' . $bundleKey])) {
      $element_info['provides']['entity_created']['bundle'] = $element->settings['param_' . $bundleKey];
    }
  }
}

/**
 * Action: Save entities.
 */
function rules_action_entity_save($wrapper, $immediate = FALSE, $settings, $state, $element) {
  $state->saveChanges($settings['data:select'], $wrapper, $immediate);
}

/**
 * Action: Delete entities.
 */
function rules_action_entity_delete($wrapper, $settings, $state, $element) {
  try {
    $wrapper->delete();
  }
  catch (EntityMetadataWrapperException $e) {
    throw new RulesException($e->getMessage(), array(), $element);
  }
}

/**
 * Action: Add a list item.
 */
function rules_action_data_list_add($list, $item, $pos = 'end', $settings) {
  switch ($pos) {
    case 'start':
      array_unshift($list, $item);
      break;

    default:
      $list[] = $item;
      break;
  }
  return array('list' => $list);
}

/**
 * Action: Remove a list item.
 */
function rules_action_data_list_remove($list, $item) {
  foreach (array_keys($list, $item) as $key) {
    unset($list[$key]);
  }
  return array('list' => $list);
}

/**
 * Action: Add variable.
 */
function rules_action_variable_add($args, $element) {
  return array('variable_added' => $args['value']);
}

/**
 * Info alteration callback for variable add action.
 */
function rules_action_variable_add_info_alter(&$element_info, RulesAbstractPlugin $element) {
  if (isset($element->settings['type']) && $type = $element->settings['type']) {
    $cache = rules_get_cache();
    $type_info = $cache['data_info'][$type];
    $element_info['parameter']['value']['type'] = $type;
    $element_info['parameter']['value']['label'] = $type_info['label'];
    $element_info['provides']['variable_added']['type'] = $type;
  }
}

/**
 * Action: Create data.
 */
function rules_action_data_create($args, $element) {
  $type = $args['type'];
  $values = array();
  foreach ($element->pluginParameterInfo() as $name => $info) {
    if ($name != 'type') {
      // Remove the parameter name prefix 'param_'.
      $values[substr($name, 6)] = $args[$name];
    }
  }
  $cache = rules_get_cache();
  $type_info = $cache['data_info'][$type];
  if (isset($type_info['creation callback'])) {
    try {
      $data = $type_info['creation callback']($values, $type);
      return array('data_created' => $data);
    }
    catch (EntityMetadataWrapperException $e) {
      throw new RulesException('Unable to create @data": ' . $e->getMessage(), array('@data' => $type), $element);
    }
  }
  else {
    throw new RulesException('Unable to create @data, no creation callback found.', array('@data' => $type), $element);
  }
}

/**
 * Info alteration callback for data create action.
 */
function rules_action_data_create_info_alter(&$element_info, RulesAbstractPlugin $element) {
  if (!empty($element->settings['type'])) {
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
  }
}

/**
 * Creation callback for array structured data.
 */
function rules_action_data_create_array($values = array(), $type) {
  // $values is an array already, so we can just pass it to the wrapper.
  return rules_wrap_data($values, array('type' => $type));
}

/**
 * Condition: Compare data.
 */
function rules_condition_data_is($data, $op, $value) {
  switch ($op) {
    default:
    case '==':
      return $data == $value || is_array($value) && in_array($data, $value);
    case '<':
      return $data < $value;
    case '>':
      return $data > $value;
    case 'contains':
      return is_string($data) && strpos($data, $value) !== FALSE || is_array($data) && in_array($value, $data);
  }
}

/**
 * Info alteration callback for the data_is condition.
 *
 * If we check the bundle property of a variable, add an assertion so that later
 * evaluated elements can make use of this information.
 */
function rules_condition_data_is_info_alter(&$element_info, RulesAbstractPlugin $element) {
  $element->settings += array('data:select' => NULL);
  if ($wrapper = $element->applyDataSelector($element->settings['data:select'])) {
    $element_info['parameter']['value']['type'] = $wrapper->type();
    $element_info['parameter']['value']['options list']  = $wrapper->optionsList() ? 'rules_data_selector_options_list' : FALSE;
  }
}

/**
 * Condition: Data value is empty.
 */
function rules_condition_data_is_empty($data) {
  return empty($data);
}

/**
 * Condition: Entity is new.
 */
function rules_condition_entity_is_new($wrapper, $settings, $state, $element) {
  return !$wrapper->getIdentifier() || !empty($entity->is_new);
}

/**
 * Condition: Entity has field.
 */
function rules_condition_entity_has_field($wrapper, $field_name, $settings, $state) {
  return isset($wrapper->$field_name) || isset($entity->$field_name);
}

/**
 * Condition: Entity is of type.
 */
function rules_condition_entity_is_of_type($wrapper, $type) {
  return $wrapper->type() == $type;
}
