<?php

/**
 * This is a smally proxy for the real rule. It provides some useful operations for the admin UI.
 * It builds a small index for the elements of a rule, so that they can be easily identified and modified.
 */
class rules_admin_rule_proxy {

  var $_rule;
  var $_counter;
  var $map = array();

  /**
   * Constructor
   * @param $rule The rule to create a proxy for.
   */
  function rules_admin_rule_proxy(&$rule) {
    $rule += array('#conditions' => array(), '#actions' => array());

    //save a copy of the original rule for later
    $this->_rule = $rule;
    
    //and the reference
    $this->map[0] = &$rule;

    $this->_counter = 0;
    $this->_generate_index($rule['#conditions']);
    $this->_generate_index($rule['#actions']);
  }

  /**
   * Gets the referenced rule
   */
  function &get_rule() {
    return $this->get_element(0);
  }

  /**
   * Gets an element of the referenced rule by id
   */
  function &get_element($id) {
    if (!isset($this->map[$id])) {
      $this->map[$id] = FALSE;
    }
    return $this->map[$id];
  }

  /**
   * Gets the id of the parent element
   */
  function get_element_parent_id($id) {
    $element = $this->get_element($id);
    while ($id > 0) {
      $id--;
      //get the element and look if it's the parent
      $parent = $this->get_element($rule_name, $elements, $id);
      foreach (element_children($parent) as $key) {
        if ($parent[$key] == $element) {
          //parent found!
          return $id;
        }
      }
    }
    return FALSE;
  }

  function save_changes() {
    $rule = $this->get_rule();
    $orig_rule = $this->_rule;
    if ($orig_rule != $rule) {
      $this->save();
      drupal_set_message(t("The rule %label has been updated.", array('%label' => $rule['#label'])));
    }
  }

  /**
   * Creates an id for each element and stores a reference on it
   */
  function _generate_index(&$elements) {
    //sort the elements, so that the elements with higher ids are executed later
    uasort($elements, "element_sort");

    //generate ids
    $this->_counter++;
    $this->map[$this->_counter] = &$elements;

    //recurse
    foreach (element_children($elements) as $key) {
      $this->_generate_index($elements[$key]);
    }
  }

  /**
   * Gets the rule with set #id properties, useful for rendering.
   * Note: Any possible changes done, won't appear in the returned rule.
   */
  function get_indexed_rule() {
    $this->_counter = 0;
    $index_rule = $this->_rule;
    $this->_generate_index_rule($index_rule['#conditions']);
    $this->_generate_index_rule($index_rule['#actions']);
    return $index_rule;
  }

  function _generate_index_rule(&$elements) {
    //generate ids
    $this->_counter++;
    $elements['#id'] = $this->_counter;

    //recurse
    foreach (element_children($elements) as $key) {
      $this->_generate_index_rule($elements[$key]);
    }
  }
  
  
  /**
   * Saves a the rule in the database
   */
  function save() {
    $rule = $this->get_rule();

    if (strpos($rule['#name'], 'rules') === TRUE) {
      $rule['#altered'] = TRUE;
    }
    rules_item_save('rules', $rule['#name'], $rule);
  }

  /**
   * Deletes the rule configuration from the database
   */
  function delete_rule() {
    $rule = $this->get_rule();
    rules_item_delete('rules', $rule['#name'], $rule);
  }
}
