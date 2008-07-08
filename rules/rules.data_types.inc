<?php

/**
 * @file Defines the data type class
 */

/**
 * Gets the right data object for the given argument information
 */
function rules_get_data_object($info) {
  $class = 'rules_data_type_'. $info['type'];
  $class = class_exists($class) ? $class : 'rules_data_type';

  return new $class;
}


/**
 * Base data type class, from which special data types can be derived.
 * @ingroup rules
 */
class rules_data_type {

  var $_data;

  /**
   * Inititate the data
   * @param $data If available, the actual argument.
   */
  function init(&$data) {
    $this->_data = &$data;
  }

  /**
   * Replaces the data with the new one
   */
  function update(&$data) {
    $this->_data = &$data;
  }

  /**
   * Gets the data
   */
  function &get() {
    return $this->_data;
  }

  /**
   * Returns whether this data is saveable
   */
  function is_saveable() {
    $info = $this->get_info();
    return isset($info['saveable']) && $info['saveable'];
  }

  /**
   * Returns whether this data is identifiable
   */
  function is_identifiable() {
    $info = $this->get_info();
    return isset($info['identifiable']) && $info['identifiable'];
  }

  /**
   * Returns whether the input evaluator should be used for this data
   */
  function eval_input() {
    $info = $this->get_info();
    return isset($info['eval_input']) && $info['eval_input'];
  }




  /**
   * Defines the capabilites of this data type.
   * If php4 would support interfaces, this would be interfaces.
   */
  function get_info() {
    return array(
      'saveable' => FALSE,
      'identifiable' => FALSE,
      'eval_input' => FALSE,
    );
  }

  /**
   * Makes changes to the data permanent.
   * Implement it, if your data type is saveable.
   */
  function save() {

  }

  /**
   * Loads the data identified with an identifier as returned by get_identifier()
   * Implement it, if your data type is identifiable.
   */
  function load($identifier) {

  }

  /**
   * Gets the identifier of this data, can be of every php data type - even an array.
   * Implement it, if your data type is identifiable.
   */
  function get_identifier() {

  }

  /**
   * Gets the input form for the data
   * Implement it, if your data is not identifiable.
   */
  function get_default_input_form($variable_info, $value) {

  }

}




/**
 * Rules string data type
 * @ingroup rules
 */
class rules_data_type_string extends rules_data_type {

  function get_info() {
    return array(
      'saveable' => FALSE,
      'identifiable' => FALSE,
      'eval_input' => TRUE,
    );
  }

  /**
   * Gets the input form for the data
   * Implement it, if your data is not identifiable.
   */
  function get_default_input_form($info, $value) {
    $info += array('long' => FALSE, 'required' => TRUE, 'description' => '');
    return array(
      '#type' => $info['long'] ? 'textfield' : 'textarea',
      '#title' => $info['label'],
      '#description' => $info['description'],
      '#required' => $info['required'],
      '#default_value' => $value,
    );
  }

}