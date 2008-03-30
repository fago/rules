<?php

/**
 * @file Provides functions and classes for handling arguments
 */


/**
 * Processes the given arguments and initializes the arguments data of the state
 *
 * Note that the order of the arguments will be kept, as it might be important for passing
 * the arguments to further argument loading handlers.
 *
 * @param $state The current evaluation state
 * @param $args The argument data passed with the event invocation
 */
function _rules_initialize_arguments(&$state, $args) {
  $argument_names = array_keys($state['set_info']['arguments']);

  if (isset($args[0]) && count($args) == 1 && is_array($args[0]) && count($args[0]) > 0 && array_diff(array_keys($args[0]), $argument_names) === array()) {
    //the caller passed an array of arguments, so let's use that
    $data = $args[0];
  }
  else {
    foreach ($args as $index => $arg) {
      $data[ $argument_names[$index] ] = $arg;
    }
  }
  $state['arguments'] = array();
  foreach ($state['set_info']['arguments'] as $name => $info) {
    $argument = new rules_argument;
    if ($argument->construct($state, $name, isset($data[$name]) ? $data[$name] : NULL)) {
      $state['arguments'][$name] = $argument;
    }
  }
}

/**
 * Gets an array of entities.
 * @param $names An array of argument names to get
 * @param $state The current evalutation state
 * @param $load Use argument handler to load arguments, if necessary.
 * @return The array of entities or FALSE. 
 */
function rules_get_arguments($names, &$state, $load = TRUE) {
  $args = array();
  foreach ($names as $name) {
    if (isset($state['arguments'][$name])) {
      $args[$name] = &$state['arguments'][$name]->get($load);
    }
    else {
      return FALSE;
    }
  }
  return $args;
}

/**
 * Maps the specified arguments of an element to the argument names of the currently
 * evaluated set.
 * 
 * @param $element The configured element, which arguments to map
 * @param $state The current evaluation state
 * @return The mapped argument names.
 */
function rules_get_mapped_argument_names($element, &$state) {
  $element_info = rules_get_element_info($element, $state);
  $reverse_map = array_flip($element['#argument map']);

  if ($element_info) {
    //get the right execution arguments by applying the argument map of this element
    $mapped_args = array();
    foreach ($element_info['arguments'] as $argument_name => $info) {
      $mapped_args[] = $reverse_map[$argument_name];
    }
    return $mapped_args;
  }
  return array();
}

/**
 * Returns the execution arguments needed by the given element
 * It applies the #argument map and gets all needed arguments.
 * 
 * @param $element The configured element, which is to be executed
 * @param $state The current evaluation state
 * @return If not all execution arguments are available, it returns FALSE
 */
function rules_get_execution_arguments($element, &$state) {
  //first off map the names to the argument names of the evaluated set
  $names = rules_get_mapped_argument_names($element, $state);

  if ($exec_args = rules_get_arguments($names, $state)) {
    //we always append some other useful variables
    $settings = isset($element['#settings']) ? $element['#settings'] : array();
    $settings += array('#argument map' => $element['#argument map']);
    $exec_args[] = $settings;
    $exec_args[] = &$state;
    return $exec_args;
  }
  return FALSE;
}

/**
 * Adds the new arguments to the evaluation state
 */
function rules_add_new_arguments($element, $new_arguments, &$state) {
  foreach ($element['#new arguments'] as $set_name => $info) {
    $map[$info['#default_name']] = $set_name;
  }
  foreach ($new_arguments as $name => $data) {
    $info = $element['#new arguments'][$set_name];
    $argument = new rules_argument;
    if ($argument->construct($state, $map[$name], &$new_arguments[$name], $info)) {
      $state['arguments'][ $map[$name] ] = $argument;
      rules_log($log, t('Successfully added the new argument @arg', array('@arg' => $info['label'])));
    }
  }
}



/**
 * Base entity class, from which special entites are derived.
 *
 * This class would be abstract in PHP5, but PHP4 doesn't understand that.
 *
 */
class rules_argument {

  var $name;
  var $info;
  var $entity;
  var $_state;
  var $_changed = FALSE;

  /**
   * Constructor
   * @param $state The current evaluation state
   * @param $name The name of the given argument
   * @param $data If available, the actual argument.
   * @param $info If given, the info for the argument. If not given it will be retrieved
   *   from the current's set info.
   * @return If the argument name isn't valid, FALSE.
   */
  function construct(&$state, $name, $data = NULL, $info = NULL) {
    $_state = &$state;
    $info = isset($info)? $info : $state['set_info']['arguments'][$name];

    if (isset($info)) {
      $this->info = array('saved' => FALSE, 'handler' => '') + $info;
      $this->name = $name;
      $this->_set_entity($data);
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets the actual entity. Be sure to keep the reference intact.
   * 
   * @param $load Use the argument handler to load the entity, if necessary.
   * @return The entity or NULL.
   */
  function &get($load = TRUE) {
    $data = NULL;

    if ($load && !isset($this->entity) && function_exists($this->info['handler'])) {
      //call the handler to get the runtime data
      $args = rules_get_arguments(array_keys($state['arguments']), $this->_state, FALSE);
      $this->_set_entity( call_user_func_array($this->info['handler'], $args) );

      $this->info['handler'] = ''; //do not invoke it twice, if it fails
      rules_log($state, t('Loaded argument @arg', array('@arg' => $this->info['label'])));
    }
    if (isset($this->entity)) {
      $data = &$this->entity->get();
    }
    return $data;
  }

  function _set_entity($data = NULL) {
    if (isset($data)) {
      $class = 'rules_entity_'. $this->info['entity'];
      $class = class_exists($class) ? $class : 'rules_entity';

      $this->entity = new $class;
      $this->entity->construct($this->_state, $data);
    }
  }

  /**
   * Marks the argument to be saved.
   */
  function save() {
    $this->_changed = TRUE;
  }

  /**
   * Updates the actual argument
   */
  function update(&$data) {
    $this->entity->update($data);
  }

  /**
   * Saves the argument to db, if necessary
   */
  function save_changes() {
    //if the argument is not saved automatically, save it
    if ($this->_changed && !$this->info['saved']) {
      $return = $this->entity->save();

      if ($return) {
        rules_log($this->_state, t('Saved entity @name of type @type.', array('@name' => $this->info['label'], '@type' => $this->info['entity'])));
      }
      else if ($return === FALSE) {
        rules_log($this->_state, t('Failed saving entity @name of type @type.', array('@name' => $this->info['label'], '@type' => $this->info['entity'])));
      }
      //else saving is not supported
    }
  }

}



/**
 * Base entity class, from which special entites can be derived.
 */
class rules_entity {

  var $_state;
  var $_data;

  /**
   * Constructor
   * @param $state The current evaluation state
   * @param $data If available, the actual argument.
   */
  function construct(&$state, &$data) {
    $this->_state = &$state;
    $this->_data = &$data;
  }
  
  function update(&$data) {
    $this->_data = &$data;
  }
  
  function &get() {
    return $this->_data;
  }

  function load() {
  
  }
  
  function save() {
  
  }

}

