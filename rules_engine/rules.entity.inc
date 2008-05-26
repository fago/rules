<?php

/**
 * @file Defines the entity class
 */


/**
 * Base entity class, from which special entites can be derived.
 * @ingroup rules
 */
class rules_entity {

  var $_data;

  /**
   * Constructor
   * @param $data If available, the actual argument.
   */
  function construct(&$data) {
    $this->_data = &$data;
  }
  
  /**
   * Replaces the entity with the new one 
   */
  function update(&$data) {
    $this->_data = &$data;
  }
  
  /**
   * Gets the entity 
   */
  function &get() {
    return $this->_data;
  }
  
  /**
   * Returns whether this entity is saveable 
   */
  function is_saveable() {
    $info = $this->get_info();
    return $info['saveable'];
  }
  
  /**
   * Returns whether this entity is identifiable 
   */
  function is_identifiable() {
    $info = $this->get_info();
    return $info['identifiable'];
  }

  /**
   * Defines the capabilites of the entity.
   * If php4 would support interfaces, this would be interfaces.
   */
  function get_info() {
    return array(
      'saveable' => FALSE,
      'identifiable' => FALSE,
    );
  }

  /**
   * Makes changes to the entity permanent.
   * Implement it, if your entity is saveable.
   */
  function save() {
  
  }

  /**
   * Loads the entity identified with an identifier as returned by get_identifier()
   * Implement it, if your entity is identifiable.
   */
  function load($identifier) {
  
  }

  /**
   * Gets the identifier of this entity, can be of every data type - even an array.
   * Implement it, if your entity is identifiable.
   */
  function get_identifier() {
    
  }

}

