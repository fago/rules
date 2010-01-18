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
 * Customize access check.
 */
function rules_action_data_set_access_check(RulesAbstractPlugin $element) {
  if (!RulesAbstractPluginDefaults::access($element)) {
    return FALSE;
  }
  if (isset($element->settings['data:select']) && $wrapper = $element->call('applyDataSelector', array($element->settings['data:select']))) {
    return $wrapper->access('edit');
  }
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

/**
 * If we check the bundle property of a variable, add an assertion so that later
 * evaluated elements can make use of this information.
 */
function rules_data_is_process_settings(RulesAbstractPlugin $element) {
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
 * Wraps lists, which in the end are numerically indexed arrays.
 */
class RulesListWrapper extends RulesDataWrapper {

  protected $itemName = 'list';

  public static function availableParameter($type) {
    $cache = rules_get_cache();
    foreach ($cache['data_info'] as $name => $info) {
      $return[$name] = t('list of @item-type items', array('@item-type' => $info['label']));
    }
    return isset($return) ? $return : array();
  }

  /**
   * Override to pass a parametrized type as the metadata wrapper can handle it.
   *
   * @return EntityMetadataWrapper
   */
  public function metadataWrapper(array $state = NULL) {
    if (!isset($this->wrapper)) {
      $data = &$this->get($state);
      $type = $this->parameter ? $type . '<' . $this->parameter . '>' : $type;
      $this->wrapper = entity_metadata_wrapper($type, $data);
      // Register this as the variable for the wrapper in the state's map.
      if (!empty($state['wrapper map'])) {
        $state['wrapper map']->add($this->wrapper, $this);
      }
    }
    return $this->wrapper;
  }
}

/**
 * Defines a generic wrapper for all core entities.
 */
class RulesEntityWrapper extends RulesDataWrapper implements RulesDataIdentifiableInterface {

  /**
   * Returns the available parameter for entities, which are the known bundles.
   */
  public static function availableParameter($type) {
    if (($info = entity_get_info($type)) && !empty($info['bundles'])) {
      foreach ($info['bundles'] as $name => $bundle_info) {
        $return[$name] = $bundle_info['label'];
      }
    }
    return isset($return) ? $return : array();
  }

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