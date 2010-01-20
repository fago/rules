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
  $wrapper = $element->call('applyDataSelector', array($settings['data:select'], $state));
  try {
    // Update the value first then save changes, if possible.
    $wrapper->set($value);
  }
  catch (EntityMetadataWrapperException $e) {
    throw new RulesException('Unable to modify data "@selector": ' . $e->getMessage(), array('@selector' => $settings['data:select']));
  }
  $info = $wrapper->info();
  // We always have to save the changes in the parent entity. E.g. when the node
  // author is changed, we don't want to save the author but the node.
  RulesDataWrapper::metadataWrapperSaveChanges($info['parent'], $state);
}

/**
 * Customize access check.
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
 * Action: Fetch data
 */
function rules_action_data_fetch($type) {
  $args = func_get_args();
  // Get the arguments for the loadParameter().
  // After the defined parameters, 4 additonals are added, which we remove.
  $ids = array_slice($args, 1, count($args)-5);
  // If it's a single id, remove the array.
  $ids = count($ids) == 1 ? reset($ids) : $ids;
  // Create a data wrapper, so the actually data is lazy loaded once needed.
  $data = rules_wrap_data($ids, array('type' => $type, 'loaded' => FALSE));
  return array('data_fetched' => $data);
}

function rules_action_data_fetch_validate() {
  $cache = rules_get_cache();
  if (!isset($element->settings['type']) || !isset($cache['data_info'][$element->settings['type']]) || !RulesExtendable::itemFacesAs($cache['data_info'][$element->settings['type']], 'RulesDataIdentifiableInterface')) {
    throw new RulesException('Invalid or not identifiable data type specified.');
  }
}

function rules_action_data_fetch_process(RulesAbstractPlugin $element) {
  $data = rules_wrap_data($data = NULL, array('type' => $element->settings['type']));
  // Add the data type's needed parameter for loading to the parameter info.
  $info['parameter'] = $data->loadParameter();
  $info['provides']['data_fetched']['type'] = $element->settings['type'];
  $element->alterInfo($info);
}

/**
 * Action: Save data
 */
function rules_action_data_save($data, $immediate = FALSE, $settings, $state, $element) {
  // Usually rules cares for saving, however it won't happen immediately.
  if ($immediate && $wrapper = $element->call('applyDataSelector', array($settings['data:select'], $state))) {
    RulesDataWrapper::metadataWrapperSaveChanges($wrapper, $state, $immediate);
    // We have already saved, so don't do it again.
    return FALSE;
  }
}

/**
 * Action: Delete data
 */
function rules_action_data_delete($data, $settings, $state, $element) {
  // Usually rules cares for saving, however it won't happen immediately.
  $wrapper = $element->call('applyDataSelector', array($settings['data:select'], $state));
  if ($var = $state['wrapper map']->getVariable($wrapper)) {
    $var->delete();
  }
  else {
    // Create a new rules data wrapper just for deletion.
    rules_wrap_data($data, $wrapper->info())->delete();
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
 * Wraps lists, which in the end are numerically indexed arrays.
 */
class RulesDataListWrapper extends RulesDataWrapper {

  protected $itemName = 'list';

  public static function availableTypeParameter($type = 'list') {
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
class RulesDataEntityWrapper extends RulesDataWrapper implements RulesDataIdentifiableInterface {

  /**
   * Returns the available parameter for entities, which are the known bundles.
   */
  public static function availableTypeParameter($type) {
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
    $info = entity_get_info($this->type);
    $name_key = isset($info['object keys']['name']) ? $info['object keys']['name'] : $info['object keys']['id'];
    $entity = $this->get();
    if (!isset($entity->$name_key)) {
      throw new RulesException('@entity is unsaved, thus there is no identifier yet.', array('@entity' => drupal_ucfirst($this->type)));
    }
    return $entity->$name_key;
  }

  /**
   * Implements RulesDataIdentifiableInterface.
   */
  function load($ids) {
    if (isset($ids[1])) {
      $info = entity_get_info($this->type);
      $conditions = array($info['object keys']['revision'] => $ids[1]);
    }
    $id = is_array($ids) ? reset($ids) : $ids;
    $result = entity_load($this->type, array($id), isset($conditions) ? $conditions : array());
    if (empty($result[$id])) {
      throw new RulesException('Unable to load @entity with id "@id"', array('@id' => $id, '@entity' => $this->type));
    }
    return $result[$id];
  }

  function loadParameter() {
    $info = entity_get_info($this->type);
    $type = isset($info['object keys']['name']) ? 'text' : 'integer';
    $return = array('id' => array('type' => $type, 'label' => t('Identifier')));
    if (isset($info['object keys']['revision'])) {
      $return += array('revision_id' => array(
        'type' => 'integer',
        'label' => t('Revision identifier'),
        'optional' => TRUE,
      ));
    }
    return $return;
  }
}

/**
 * Extends entities to be savable, creatable and deletable.
 */
class RulesDataEntityExtender extends RulesDataWrapper implements FacesExtenderInterface {

  function implementsFaces() {
    return array('RulesDataSavableInterface', 'RulesDataDeletableInterface', 'RulesDataCreatableInterface', 'RulesDataCRUDInterface');
  }

  function save($object) {
    entity_metadata_entity_save($object->type, $object->get());
  }

  function delete($object) {
    entity_metadata_entity_delete($object->type, $object->getIdentifier());
  }

  function create($values = array(), $object) {
    $object->data = entity_metadata_entity_create($object->type, $values);
  }

  function creationParameter($object) {
    $return = array();
    foreach ($object->metadataWrapper() as $name => $info) {
      if (!empty($info['required'])) {
        $return[$name] = array('type' => $info['type'], 'label' => $info['label']);
      }
    }
    return $return;
  }
}
