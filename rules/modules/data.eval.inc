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
  $wrapper = $state['execution arguments']['data'];
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
 * Action: Fetch data
 */
function rules_action_data_fetch($type) {
  $args = func_get_args();
  // Get the arguments for the loadParameter().
  // Settings is always the forth last parameter that is passed.
  $settings = $args[count($args)-4];
  $ids = array();
  foreach (array_keys($settings['#info']['parameter']) as $i => $name) {
    // Remove the parameter name prefix 'param_'.
    $ids[substr($name, 6)] = $args[$i+1];
  }
  // If it's a single id, remove the array.
  $ids = count($ids) == 1 ? reset($ids) : $ids;
  // Create a data wrapper, so the actually data is lazy loaded once needed.
  $data = rules_wrap_data($ids, array('type' => $type, 'loaded' => FALSE));
  return array('data_fetched' => $data);
}

/**
 * Action: Create data
 */
function rules_action_data_create($type) {
  $args = func_get_args();
  // Settings is always the forth last parameter that is passed.
  $settings = $args[count($args)-4];
  $values = array();
  foreach (array_keys($settings['#info']['parameter']) as $i => $name) {
    // Remove the parameter name prefix 'param_'.
    $values[substr($name, 6)] = $args[$i+1];
  }
  $data = rules_wrap_data($data = NULL, array('type' => $type));
  $data->create($values);
  return array('data_created' => $data);
}

/**
 * Action: Save data
 */
function rules_action_data_save($data, $immediate = FALSE, $settings, $state, $element) {
  // Usually rules cares for saving, however it won't happen immediately.
  if ($immediate && $wrapper = $state['execution arguments']['data']) {
    RulesDataWrapper::metadataWrapperSaveChanges($wrapper, $state, $immediate);
    // We have already saved, so don't do it again.
    return FALSE;
  }
}

/**
 * Action: Delete data
 */
function rules_action_data_delete($data, $settings, $state, $element) {
  $wrapper = $state['execution arguments']['data'];
  if ($var = $state['wrapper map']->getVariable($wrapper)) {
    $var->delete();
  }
  else {
    // Create a new rules data wrapper just for deletion.
    rules_wrap_data($data, array('type' => $wrapper->type()))->delete();
  }
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
class RulesDataListWrapper extends RulesDataWrapper implements RulesDataMetadataWrapperInterface {

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
      $type = $this->parameter ? 'list' . '<' . $this->parameter . '>' : $this->type;
      $this->wrapper = entity_metadata_wrapper($type, $data);
    }
    return $this->wrapper;
  }
}

/**
 * Defines a generic wrapper for all core entities.
 */
class RulesDataEntityWrapper extends RulesDataWrapper implements RulesDataIdentifiableInterface, RulesDataAccessInterface {

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

  /**
   * Implements RulesDataAccessInterface.
   * Incorporate access permissions defined by the entity metadata module.
   */
  function access($op) {
    return entity_metadata_entity_access($op, $this->type);
  }

  /**
   * Implements RulesDataMetadataWrapperInterface.
   */
  public function metadataWrapper(array $state = NULL) {
    if (!isset($this->wrapper)) {
      $data = &$this->get($state);
      // We treat the type parameter as bundle.
      $this->wrapper = entity_metadata_wrapper($this->type, $data, $this->parameter ? array('bundle' => $this->parameter) : array());
      // Register this as the variable for the wrapper in the state's map.
      if (!empty($state['wrapper map'])) {
        $state['wrapper map']->add($this->wrapper, $this);
      }
    }
    return $this->wrapper;
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
        $info += array('type' => 'text');
        $return[$name] = array('type' => $info['type'], 'label' => $info['label']);
      }
    }
    return $return;
  }
}
