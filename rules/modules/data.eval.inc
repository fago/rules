<?php
// $Id$

/**
 * @file
 * Contains often used rules data wrappers
 *
 * @addtogroup rules
 * @{
 */

/**
 * Action: Modify data.
 */
function rules_action_data_set($data, $value, $settings, $state, $element) {
  $wrapper = $state->currentArguments['data'];
  try {
    // Update the value first then save changes, if possible.
    $wrapper->set($value);
  }
  catch (EntityMetadataWrapperException $e) {
    throw new RulesException('Unable to modify data "@selector": ' . $e->getMessage(), array('@selector' => $settings['data:select']));
  }
  $info = $wrapper->info();
  // We always have to save the changes in the parent entity. E.g. when the node
  // author is changed, we don't want to save the author but the node. So use
  // the parent wrapper and also cut off the last part of the selector.
  $state->saveChanges(implode(':', explode(':', $settings['data:select'], -1)), $info['parent']);
}

/**
 * Action: Fetch data
 */
function rules_action_entity_fetch($type, $id, $revision) {
  $info = entity_get_info($type);

  // Support the revision parameter, if applicable.
  if (isset($info['object keys']['revision']) && isset($revision)) {
    $conditions = array($info['object keys']['revision'] => $revision);
  }
  $entity = reset(entity_load($type, array($id), isset($conditions) ? $conditions : array()));
  if (!$entity) {
    throw new RulesException('Unable to load @entity with id "@id"', array('@id' => $id, '@entity' => $type));
  }
  return array('entity_fetched' => $entity);
}

/**
 * Action: Create data
 */
function rules_action_entity_create($type) {
  $args = func_get_args();
  // Settings is always the forth last parameter that is passed.
  $settings = $args[count($args)-4];
  $values = array();
  foreach (array_keys($settings['#info']['parameter']) as $i => $name) {
    // Remove the parameter name prefix 'param_'.
    $values[substr($name, 6)] = $args[$i+1];
  }
  $data = entity_metadata_entity_create($type, $values)->value();
  return array('entity_created' => $data);
}

/**
 * Action: Save data
 */
function rules_action_entity_save($data, $immediate = FALSE, $settings, $state, $element) {
  $wrapper = $state->currentArguments['data'];
  $state->saveChanges($settings['data:select'], $wrapper, $immediate);
}

/**
 * Action: Delete data
 */
function rules_action_entity_delete($data, $settings, $state, $element) {
  $wrapper = $state->currentArguments['data'];
  $wrapper->delete();
}

/**
 * Action: Add a list item
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
 * Action: Remove a list item
 */
function rules_action_data_list_remove($list, $item) {
  foreach (array_keys($list, $item) as $key) {
    unset($list[$key]);
  }
  return array('list' => $list);
}

/**
 * Condition: Compare data
 */
function rules_condition_data_is($data, $op, $value) {
  switch ($op) {
    default:
    case '=':
      return $data == $value;
    case '<=':
      return $data <= $value;
    case '>=':
      return $data >= $value;
    case 'IN':
      return is_array($value) && in_array($data, $value);
  }
}

/*
Classes are not used but keep the comments for now.

class RulesTextWrapper extends RulesData {
  // TODO: input form
  // TODO: use input evaluator in input form

  protected $itemName = 'text';
}

class RulesDecimalWrapper extends RulesTextWrapper {
  // TODO: input form
  // TODO: use input evaluator in input form (calculation)

  protected $itemName = 'decimal';
}

class RulesIntegerWrapper extends RulesDecimalWrapper {
  // TODO: input form
  // TODO: use input evaluator in input form (calculation)

  protected $itemName = 'integer';
}

class RulesDateWrapper extends RulesData {
  // TODO: input form
  // TODO: customize selector form and add offset (duration)

  protected $itemName = 'date';
}

class RulesDurationWrapper extends RulesData {
  // TODO: input form
  // TODO: use input evaluator in input form (calculation)

  protected $itemName = 'duration';
}

class RulesBooleanWrapper extends RulesData {
  // TODO: input form
  // TODO: customize selector form and add negation option

  protected $itemName = 'boolean';
}

class RulesURIWrapper extends RulesTextWrapper {
  // TODO: input form
  // TODO: use input evaluator in input form

  protected $itemName = 'uri';
}
*/
