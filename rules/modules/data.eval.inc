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
function rules_action_data_set($data, $value) {
  // Rules will use the data wrapper to update the value and if possible, save.
  return array('data' => $value);
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

/**
 * Ensures the 'data' argument is a data wrapper.
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


class RulesTextWrapper extends RulesDataWrapper {
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

class RulesDateWrapper extends RulesDataWrapper {
  // TODO: input form
  // TODO: customize selector form and add offset (duration)

  protected $itemName = 'date';
}

class RulesDurationWrapper extends RulesDataWrapper {
  // TODO: input form
  // TODO: use input evaluator in input form (calculation)

  protected $itemName = 'duration';
}

class RulesBooleanWrapper extends RulesDataWrapper {
  // TODO: input form
  // TODO: customize selector form and add negation option

  protected $itemName = 'boolean';
}

class RulesURIWrapper extends RulesTextWrapper {
  // TODO: input form
  // TODO: use input evaluator in input form

  protected $itemName = 'uri';
}

/**
 * Defines a generic wrapper for all core entities.
 */
class RulesEntityWrapper extends RulesDataWrapper implements RulesDataIdentifiableInterface {

  protected $itemName = 'entity';

  /**
   * Implements RulesDataIdentifiableInterface.
   */
  function getIdentifier() {
    list($id, $vid, $bundle) = entity_extract_ids($this->info['type'], $this->get());
    if (!isset($id)) {
      throw new RulesException('@entity is unsaved, thus there is no identifier yet.', array('@entity' => drupal_ucfirst($this->info['type'])));
    }
    return $id;
  }

  /**
   * Implements RulesDataIdentifiableInterface.
   */
  function load($id) {
    $result = entity_load($this->info['type'], array($id));
    if (empty($result[$id])) {
      throw new RulesException('Unable to load @entity with id "@id"', array('@id' => $id, '@entity' => $this->info['type']));
    }
    return $result[$id];
  }
}

/**
 * Defines the node data wrapper.
 */
class RulesNodeDataWrapper extends RulesEntityWrapper implements RulesDataSavableInterface {

  protected $itemName = 'node';

  /**
   * Implements RulesDataSavableInterface.
   */
  function save() {
    node_save($this->get());
  }
}

/**
 * Defines the user account data wrapper.
 */
class RulesUserDataWrapper extends RulesEntityWrapper implements RulesDataSavableInterface {

  protected $itemName = 'user';

  /**
   * Implements RulesDataSavableInterface.
   */
  function save() {
    // Automatically save all field API and regular user fields.
    $table = drupal_get_schema('users');
    $user_fields = $table['fields'];
    $account = $this->get();
    list($id, $vid, $bundle) = entity_extract_ids('user', $account);
    $instances = field_info_instances('user', $bundle);
    // Extract the values of all valid keys into $edit.
    $edit = array_intersect_key((array)$account, $user_fields + $instances);
    user_save($account, $edit);
  }
}