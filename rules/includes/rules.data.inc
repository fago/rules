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
   * Gets the identifier of this data, which can be of every php data type.
   */
  public function getIdentifier();
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
 * Interface for any data that can be specified by a direct input form.
 */
interface RulesDataDirectInputFormInterface {

  public function useEvalInput();

  /**
   * Gets an input form for creating an instance of your data type.
   */
  public function getDirectInputForm($variable_info, $value, &$form_state);

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

  protected $hook = 'data_info';

  protected $data = NULL;
  protected $name;
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
    if (!isset($this->itemName)) {
      $this->itemName = $this->info['type'];
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
  public function getInfo() {
    return $this->info;
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
      $id = $this->data;
      // First unset the reference on the data, so we prevent changing it.
      unset($this->data);
      $this->data = &$this->load($id);
    }
    return $this->data;
  }

  /**
   * Updates the data.
   *
   * @param $data
   *   The updated data.
   * @param $permanent
   *   If TRUE, save the data later on. Only applicable for savable data types.
   */
  public function update($data, $permanent = FALSE) {
    $this->data = $data;
    if ($permanent) {
      $this->saveChanges(FALSE);
    }
    return $this;
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
        rules_log('Saved variable @name of type @type.', array('@name' => $this->name, '@type' => $this->info['type']));
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

