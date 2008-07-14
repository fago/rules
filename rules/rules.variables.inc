<?php
// $Id$

/**
 * @file Provides functions and classes for handling variables
 */


/**
 * Processes the given variables and initializes the variables data of the state
 *
 * Note that the order of the arguments will be kept, as it might be important for passing
 * the variables to further variable loading handlers.
 *
 * @param $state The current evaluation state
 * @param $args The arguments passed with the event invocation
 */
function _rules_initialize_variables(&$state, $args) {
  $argument_names = array_keys($state['set_info']['arguments']);

  if (isset($args[0]) && count($args) == 1 && is_array($args[0]) && count($args[0]) > 0 && array_diff(array_keys($args[0]), $argument_names) === array()) {
    //the caller passed an array of arguments, so let's use that
    $data = $args[0];
  }
  else {
    foreach ($args as $index => $arg) {
      $data[ $argument_names[$index] ] = &$args[$index];
    }
  }
  $state['variables'] = array();
  foreach ($state['set_info']['arguments'] as $name => $info) {
    $variable = new rules_variable;
    $data += array($name => NULL);
    $variable->construct($state, $name, $data[$name]);
  }
}

/**
 * Gets an array of entities.
 * @param $names An array of variable names to get
 * @param $state The current evalutation state
 * @param $load Use variable handler to load variables, if necessary.
 * @return The array of entities or FALSE.
 */
function rules_get_variables($names, &$state, $load = TRUE) {
  $args = array();
  foreach ($names as $name) {
    if (isset($state['variables'][$name])) {
      $args[$name] = &$state['variables'][$name]->get($load);
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
 * @param $element The configured element, which variables to map
 * @param $state The current evaluation state
 * @return The mapped argument names.
 */
function rules_get_mapped_argument_names($element) {
  $element_info = rules_get_element_info($element);
  $reverse_map = array_flip($element['#settings']['#argument map']);

  if ($element_info) {
    //get the right execution variables by applying the argument map of this element
    $mapped_args = array();
    foreach ($element_info['arguments'] as $argument_name => $info) {
      $mapped_args[$argument_name] = isset($reverse_map[$argument_name]) ? $reverse_map[$argument_name] : $argument_name;
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
  $element['#settings'] += array('#argument map' => array());
  //first off map the names to the argument names of the evaluated set
  $names = rules_get_mapped_argument_names($element);
  $exec_args = rules_get_variables($names, $state);

  if ($exec_args !== FALSE) {
    //we always append some other useful variables
    $settings = isset($element['#settings']) ? $element['#settings'] : array();
    $exec_args[] = $settings;
    $exec_args[] = &$state;
    return $exec_args;
  }
  rules_log(t('Element %name has not been executed. There are not all execution arguments available.', array('%name' => rules_get_element_label($element))));
  return FALSE;
}

/**
 * Saves variables, which are returned by an action
 */
function rules_save_variables($element, $result, &$state) {
  $element_info = rules_get_element_info($element);
  $reverse_map = array_flip($element['#settings']['#argument map']);

  foreach (element_children($result) as $argument_name) {
    $reverse_map += array($argument_name => $argument_name);

    if (isset($state['variables'][ $reverse_map[$argument_name] ])) {
      //variable exists, so update it
      $variable = &$state['variables'][ $reverse_map[$argument_name] ];
      $variable->update( $result[$argument_name] );
      $variable->save();
    }
    else if (isset($element_info['new variables'][ $reverse_map[$argument_name] ])) {
      //it is a new variable, so add it
      $info = $element_info['new variables'][ $reverse_map[$argument_name] ];

      $variable = new rules_variable;
      $variable->construct($state, $reverse_map[$argument_name], $result[$argument_name], $info);

      rules_log(t('Successfully added the new variable @arg', array('@arg' => $info['label'])));
    }
    else {
      rules_log(t('Unknown variable name %var return by action %name.', array('%var' => $argument_name, '%name' => $element['#name'])), TRUE);
    }
  }
}

/**
 * Handles a variable
 */
class rules_variable {

  var $name;
  var $info;
  var $data;
  var $_state;
  var $_changed = FALSE;

  /**
   * Constructor
   * @param $state The current evaluation state
   * @param $name The name of the given variable
   * @param $data If available, the actual data, else NULL.
   * @param $info If given, the info for the variable. If not given it will be retrieved
   *   from the current's set info.
   * @return If the variable name isn't valid, FALSE.
   */
  function construct(&$state, $name, &$data, $info = NULL) {
    $this->_state = &$state;
    $info = isset($info)? $info : $state['set_info']['arguments'][$name];

    if (isset($info)) {
      $this->info = $info + array('saved' => FALSE, 'handler' => '');
      $this->name = $name;
      $this->_set_data($data);
      $state['variables'][$name] = &$this;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Gets the actual data. Be sure to keep the reference intact.
   *
   * @param $load Use the variable handler to load the variable, if necessary.
   * @return The data or NULL.
   */
  function &get($load = TRUE) {
    $data = NULL;

    if ($load && !isset($this->data) && function_exists($this->info['handler'])) {
      //call the handler to get the runtime data
      $args = rules_get_variables(array_keys($this->_state['variables']), $this->_state, FALSE);
      $data = call_user_func_array($this->info['handler'], $args);
      $this->_set_data($data);

      $this->info['handler'] = ''; //do not invoke it twice, if it fails
      rules_log(t('Loaded variable @arg', array('@arg' => $this->info['label'])));
    }
    else if (isset($this->data)) {
      $data = &$this->data->get();
    }
    return $data;
  }

  function _set_data(&$data) {
    if (isset($data)) {
      $this->data = rules_get_data_object($this->info);
      $this->data->init($data);
    }
  }

  /**
   * Marks the variable to be saved.
   */
  function save() {
    $this->_changed = TRUE;
  }

  /**
   * Updates the actual variable
   */
  function update(&$data) {
    $this->data->update($data);
  }

  /**
   * Saves the variable to db, if necessary
   */
  function save_changes() {
    //if the variable is not saved automatically, save it
    if ($this->_changed && !$this->info['saved'] && $this->data->is_saveable()) {
      $return = $this->data->save();

      if ($return) {
        rules_log(t('Saved variable @name of type @type.', array('@name' => $this->info['label'], '@type' => $this->info['type'])));
      }
      else {
        rules_log(t('Failed saving variable @name of type @type.', array('@name' => $this->info['label'], '@type' => $this->info['type'])));
      }
    }
  }

}
