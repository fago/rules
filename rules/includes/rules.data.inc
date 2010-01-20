<?php
// $Id$

/**
 * @file Contains basic rules data wrappers.
 */

/**
 * Interface for any identifiable data.
 */
interface RulesDataIdentifiableInterface {

  /**
   * Loads the data identified with an identifier as returned by getIdentifier().
   * @throws RulesException
   *   Throws a 'RulesException' if the data cannot be loaded.
   * @return
   *   The data.
   */
  public function load($identifier);

  /**
   * Gets the identifier of this data.
   *
   * @return
   *   The identifier of the data. When the data is serialized only the id will
   *   be saved, upon wakeup the identifiers is passed to load(). If you return
   *   an array, it will be treated as an array of arguments for load() on
   *   wakeup.
   */
  public function getIdentifier();

  /**
   * Describes the parameter needed for loading. Describe the data as needed for
   * load() and returned by getIdentifier().
   *
   * @return
   *   An array describing the parameters of the load method. Use the same
   *   array structure as known for action parameters.
   */
  public function loadParameter();
}

/**
 * Interface for any savable data.
 */
interface RulesDataSavableInterface extends RulesDataIdentifiableInterface {

  /**
   * Makes changes to the data permanent.
   */
  public function save();
}

/**
 * Interface for any data that can be deleted.
 */
interface RulesDataDeletableInterface {

  /**
   * Permanently deletes the data.
   */
  public function delete();
}

/**
 * Interface for any data that can be created.
 */
interface RulesDataCreatableInterface {

  /**
   * Creates a new instance of the wrapped data type.
   *
   * @param $values
   *   An array of values for properties to set. If there is entity metadata
   *   that marks some properties as required, then they have to be passed.
   */
  public function create($values = array());

  /**
   * Describes the values needed for creating.
   *
   * @return
   *   An array describing the entries of the $values parameter of the create()
   *   method. Use the same array structure as known for action parameters.
   */
  public function creationParameter();
}

/**
 * Combines the above interfaces to a CRUD Interface.
 */
interface RulesDataCRUDInterface extends RulesDataSavableInterface, RulesDataDeletableInterface, RulesDataCreatableInterface {

}




/**
 * Interface for any data that can be specified by an input form.
 */
interface RulesDataInputFormInterface {

  public function useEvalInput();

  /**
   * Gets an input form for creating an instance of your data type.
   */
  public function getInputForm($variable_info, $value, &$form_state);

  /**
   * Process or check the value of your data type. E.g. the number data type
   * uses this to make sure the value is a number.
   */
  public function processValue($variable_info, $value);
}




/**
 * A generic data wrapper.
 * Modules can provide further wrappers by inheriting or extending this class.
 *
 * Interfaces that may be used to extend a wrapper:
 *
 * @see RulesDataSavableInterface
 * @see RulesDataIdentifiableInterface
 * @see RulesDataDirectInputFormInterface
 */
class RulesDataWrapper extends RulesExtendable implements Serializable {

  /**
   * Defines the available type parameter for a data type.
   *
   * @param $type
   *   The actual type for which to get the type parameter.
   * @return
   *   An array keyed by parameter with the paremtrized type's label as values.
   */
  public static function availableTypeParameter($type) {
    return array();
  }

  /**
   * Returns whether the type match. They match if type1 is compatible to type2.
   *
   * @param $type1
   *   The name of the type to check for whether it is compatible to type2.
   * @param $type2
   *   The type expression to check for.
   * @return
   *   Whether the types match.
   */
  public static function typesMatch($type1, $type2) {
    if ($type2 == '*' || $type2 == 'unknown' || $type1 == $type2) {
      return TRUE;
    }
    // Multiple types may be specified using an array.
    $types = is_array($type2) ? $type2 : array($type2);
    if (in_array($type1, $types)) {
      return TRUE;
    }
    // In case $type1 is parametrized, try again with the unparametrized type.
    if ((list($type, $parameter) = self::typeExtractParameter($type1)) && $parameter) {
      return self::typesMatch($type, $type2);
    }
    // Check for sub-type relationships.
    $cache = &rules_get_cache();
    self::typeCalcAncestors($cache, $type1);

    // If one of the types is an ancestor return TRUE.
    return (bool)array_intersect_key($cache['data_info'][$type1]['ancestors'], array_flip($types));
  }

  protected static function typeCalcAncestors(&$cache, $type) {
    if (!isset($cache['data_info'][$type]['ancestors'])) {
      $cache['data_info'][$type]['ancestors'] = array();
      if (isset($cache['data_info'][$type]['parent']) && $parent = $cache['data_info'][$type]['parent']) {
        $cache['data_info'][$type]['ancestors'][$parent] = TRUE;
        self::typeCalcAncestors($cache, $parent);
        // Add all parent ancestors to our own ancestors.
        $cache['data_info'][$type]['ancestors'] += $cache['data_info'][$parent]['ancestors'];
      }
    }
  }

  /**
   * Splits up the given type of the form "type<parameter>" into the type and
   * the parameter.
   *
   * @return
   *   An array where the first entry is the type without the parameter and the
   *   second is the parameter, or FALSE if there is none.
   */
  public static function typeExtractParameter($type) {
    if (($pos = strpos($type, '<')) && $type[strlen($type)-1] == '>') {
      return array(substr($type, 0, $pos), substr($type, $pos + 1, -1));
    }
    return array($type, FALSE);
  }

  /**
   * Makes the changes to a metadata wrapper permanent by retrieving the proper
   * rules data wrapper and using it to save changes.
   *
   * @param $wrapper
   *   The wrapper that has been set, thus its parent will be saved.
   */
  public static function metadataWrapperSaveChanges($wrapper, array $state, $immediate = FALSE) {
    $info = $wrapper->info();
    // We always need to save the parent entity.
    if (isset($info['parent']) && $wrapper = $info['parent']) {
      if ($var = $state['wrapper map']->getVariable($wrapper)) {
        $var->update($wrapper)->saveChanges($immediate, $state);
      }
      // This data wrapper has no variable associated yet, so create one.
      elseif (($var = rules_wrap_data($wrapper->value(), $info)) && $var->isIdentifiable()) {
        if ($var->isSavable()) {
          $var->saveChanges($immediate, $state);
          $state['wrapper map']->add($wrapper, $var);
        }
        // Else the data is identifiable, but not savable. Nothing we can do.
      }
      else {
        // Data was not identifiable, so try saving the parent.
        self::metadataWrapperSaveChanges($wrapper, $state, $immediate);
      }
    }
  }

  public static function typeIsIdentifiable($type) {
    $cache = rules_get_cache();
    foreach (array('RulesDataIdentifiableInterface', 'RulesDataSavableInterface', 'RulesDataCRUDInterface') as $interface) {
      if (RulesExtendable::itemFacesAs($cache['data_info'][$type], $interface)) {
        return TRUE;
      }
    }
    return FALSE;
  }





  protected $hook = 'data_info';

  protected $data = NULL, $wrapper = NULL;
  protected $name, $type = 'unknown', $parameter;
  protected $info = array();

  /**
   * The rules to block during saving due to recursion prevention. Passed
   * through to the state during save(), thus any triggered events block those.
   */
  protected $blocked = array();

  /**
   * Constructor
   *
   * @param $data
   *   If available, the actual data, else NULL.
   * @param $info
   *   An array of info about this data.
   */
  function __construct(&$data = NULL, array $info = array(), $cache = array()) {
    $this->data = &$data;
    $this->info = $info + array(
      'skip save' => FALSE,
      'save' => FALSE,
      'type' => 'unknown',
      'handler' => FALSE,
      'loaded' => empty($info['handler']),
    );
    list($this->type, $this->parameter) = self::typeExtractParameter($this->info['type']);
    if (!isset($this->itemName)) {
      $this->itemName = $this->type;
    }
    $this->setUp();
  }

  /**
   * @return
   *   Returns TRUE if the data is already loaded, FALSE else.
   */
  public function isLoaded() {
    return $this->info['loaded'];
  }

  /**
   * This variable is registered in the execution state with the given name.
   *
   * @param $name The name of the variable that holds this data.
   */
  public function setVariableName($name) {
    $this->name = $name;
  }

  /**
   * Returns whether this data is savable
   */
  public function isSavable() {
    return $this->facesAs('RulesDataSavableInterface');
  }

  /**
   * Returns whether this data is identifiable
   */
  public function isIdentifiable() {
    return $this->facesAs('RulesDataIdentifiableInterface') || $this->facesAs('RulesDataSavableInterface');
  }

  /**
   * Returns whether this data makes use of an input form for creating an instance on the fly.
   */
  public function hasDirectInputForm() {
    return $this->facesAs('RulesDataDirectInputFormInterface');
  }

  /**
   * Gets the information about this data type.
   */
  public function info() {
    return $this->info;
  }

  /**
   * Returns the entity metadata wrapper for this data type, useful for deriving
   * properties of an entity.
   *
   * @return EntityMetadataWrapper
   */
  public function metadataWrapper(array $state = NULL) {
    if (!isset($this->wrapper)) {
      $data = &$this->get($state);
      // By default we treat the type parameter as bundle.
      $this->wrapper = entity_metadata_wrapper($this->type, $data, $this->parameter ? array('bundle' => $this->parameter) : array());
      // Register this as the variable for the wrapper in the state's map.
      if (!empty($state['wrapper map'])) {
        $state['wrapper map']->add($this->wrapper, $this);
      }
    }
    return $this->wrapper;
  }

  /**
   * Gets the actual data. Be sure to keep the reference intact.
   *
   * If this data wrapper has been configured using a direct input form (via
   * the RulesDataDirectInputFormInterface) any input evaluators are applied and
   * the value is processed.
   *
   * @param $state
   *   The current execution state. Optional, but needed for applying input
   *   evaluators.
   * @throws RulesException
   *   Throws a RulesException, if the data should be loaded, but loading fails.
   * @return The data.
   */
  public function &get(array $state = NULL) {
    // TODO: If there is an input value first of apply input eval + process it
    // to get the data ==> Support input evaluation.
    if (!$this->info['loaded'] && $this->info['handler'] && function_exists($this->info['handler'])) {
      $this->info['loaded'] = TRUE;
      $args = isset($state) ? rules_unwrap_data($state['variables'], $state, FALSE) : array();
      $this->data = call_user_func_array($this->info['handler'], $args);
    }
    elseif (!$this->info['loaded'] && isset($this->data) && $this->isIdentifiable()) {
      $this->info['loaded'] = TRUE;
      $ids = $this->data;
      // First unset the reference on the data, so we prevent changing it.
      unset($this->data);
      if (!is_array($ids)) {
        $this->data = $this->load($ids);
      }
      else {
        $this->data = call_user_func_array(array($this, 'load'), $ids);
      }
    }
    return $this->data;
  }

  /**
   * Updates the data.
   *
   * @param $data
   *   The data with which to update or its EntityMetadataWrapper.
   * @param $permanent
   *   If TRUE, save the data later on. Only applicable for savable data types.
   */
  public function update($data, $permanent = FALSE) {
    $this->data = $data instanceof EntityMetadataWrapper ? $data->value() : $data;
    if (isset($this->wrapper) && $this->wrapper != $data) {
      // Sync with the metadata wrapper, so that selectors get the fresh data.
      $this->wrapper->set($data);
    }
    return $permanent ? $this->saveChanges(FALSE) : $this;
  }

  /**
   * Apply permanent changes provided this data type is savable.
   */
  public function saveChanges($immediate = TRUE, $state = array()) {
    // Remember to save later on.
    if (!$immediate && !$this->info['skip save']) {
      $this->info['save'] = TRUE;
      if (isset($state['blocked'])) {
        $this->blocked += $state['blocked']->getArrayCopy();
      }
    }
    // Save now!
    elseif ($this->info['save'] && !$this->info['skip save'] && $this->isSavable()) {
      if (isset($this->name)) {
        rules_log('Saved variable %name of type %type.', array('%name' => $this->name, '%type' => $this->info['type']));
      }
      if (isset($state['blocked']) && !empty($this->blocked)) {
        // Add the set of blocked elements for the recursion prevention.
        $previously_blocked = $state['blocked']->getArrayCopy();
        $state['blocked']->exchangeArray($previously_blocked + $this->blocked);
      }
      // Actually save!
      $this->save();

      if (isset($state['blocked']) && isset($previously_blocked)) {
        // Restore the state's set of blocked elements.
        $state['blocked']->exchangeArray($previously_blocked);
      }
      $this->info['save'] = FALSE;
    }
    return $this;
  }

  /**
   * Implementation of the Serializable Interface.
   * We do not use __sleep() so we avoid changing the current instance.
   */
  public function serialize() {
    $data = $this->data;
    $info = $this->info;
    if ($this->isIdentifiable()) {
      // If this data type is identifiable, only serialize the id.
      $data = $this->getIdentifier();
      $info['loaded'] = FALSE;
      $info['handler'] = FALSE;
    }
    return serialize(array($data, $info, $this->skipSetUp, $this->itemName));
  }

  /**
   * Implementation of the Serializable Interface.
   */
  public function unserialize($serialized) {
    list($this->data, $this->info, $this->skipSetUp, $this->itemName) = unserialize($serialized);
    parent::__wakeup();
  }

}
