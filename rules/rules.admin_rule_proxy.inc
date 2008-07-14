<?php
// $Id$

/**
 * @file Contains the rules proxy class
 */

/**
 * This is a smally proxy for the real rule. It provides some useful operations for the admin UI.
 * It builds a small index for the elements of a rule, so that they can be easily identified and modified.
 */
class rules_admin_rule_proxy {

  var $_rule;
  var $_counter;      //used when generating ids
  var $_variables;    //variables defined by previous rules
  var $map = array(); //stores the id => element map

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
   * Gets the rule's name
   */
  function get_rule_name() {
    $rule = $this->get_rule();
    return $rule['#name'];
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
      $parent = $this->get_element($id);
      foreach (element_children($parent) as $key) {
        if ($parent[$key] == $element) {
          //parent found!
          return $id;
        }
      }
    }
    return FALSE;
  }

  /**
   * Saves the changes in the rule and clears the cache if necessary.
   */
  function save_changes() {
    $rule = $this->get_rule();
    $orig_rule = $this->_rule;
    if ($orig_rule != $rule) {
      $this->save_rule();
      rules_clear_cache();
    }
  }

  /**
   * Creates an id for each element and stores a reference on it
   */
  function _generate_index(&$elements) {
    //sort the elements
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
    //sort the elements
    uasort($elements, "element_sort");

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
  function save_rule() {
    $rule = $this->get_rule();

    if (!isset($rule['#status']) || $rule['#status'] == 'default') {
      $rule['#status'] = 'altered';
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




  /**
   * Cleans the given rule. This means, array keys that are neither elements
   * nor properties are removed.
   */
  function clean_rule() {
    $rule =& $this->get_rule();
    $this->_clean_rule($rule['#conditions']);
    $this->_clean_rule($rule['#actions']);
  }

  function _clean_rule(&$element) {
    $children = array();
    foreach (element_children($element) as $key) {
      if (!isset($element[$key]['#type'])) {
        //this element can be removed, but care for it's children
        foreach (element_children($element[$key]) as $child_key) {
          $children[$child_key] = $element[$key][$child_key];
        }
        unset($element[$key]);
      }
      else {
        $this->_clean_rule($element[$key]);
      }
    }
    if (count($children)) {
      $element = array_merge($element, $children);
    }
  }

  /**
   * Gets the set of this rule, which contains only active rules.
   * If there ar
   */
  function get_set() {
    $rule = $this->get_rule();
    $set = rules_get_rule_set($rule['#set']);
    return $set ? $set : array('info' => array(), 'rules' => array());
  }

  /**
   * Gets the info about the set of this rule
   */
  function get_set_info() {
    $rule = $this->get_rule();
    return rules_get_rule_sets($rule['#set']) + array('arguments' => array());
  }

  /**
   * Gets the info about all new defined variables in this rule,
   * before the element with the given id
   */
  function get_new_variables($id = NULL) {
    $vars = array();
    $i = 0;
    $element = TRUE;
    while (isset($id) && $i < $id || !isset($id) && $element) {
      $element = $this->get_element($i);
      $vars = $this->element_get_new_variables($element) + $vars;
      $i++;
    }
    return $vars;
  }

  /**
   * Gets info about all new defined variables by the given element
   */
  function element_get_new_variables($element) {
    if (isset($element['#type']) && $element['#type'] == 'action') {
      $info = rules_get_element_info($element);
      return $info['new variables'];
    }
    return array();
  }

  /**
   * Gets info about all available variables
   * This are the arguments of the rule's set as well as further arguments that
   * might be provided by actions of this or preceding rules
   *
   * @param $id The id of the element of the current rule until which we should get new variables.
   *   If not given all new variables of this rule will be considered.
   */
  function get_available_variables($id = NULL) {
    $set_info = $this->get_set_info();
    return $this->get_new_variables($id) + $this->_get_available_variables() + $set_info['arguments'];
  }

  /**
   * Gets info about all defined variables
   * This are all available variables as well as variables defined after the given element.
   * Variables defined by the element of the given id are left out.
   *
   * @param $id The id of the element, for which we should not add defined variables (optional)
   */
  function get_defined_variables($id = NULL) {
    $set_info = $this->get_set_info();
    $vars = $this->_get_available_variables(TRUE) + $set_info['arguments'];
    if (isset($id)) {
      $vars = array_diff_assoc($vars, $this->element_get_new_variables($this->get_element($id)));
    }
    return $vars;
  }

  /**
   * Gets all new variables defined by actions in rules, which are evaluated until this rule.
   *
   * @param $all If set to TRUE all variables defined in this rule set will returned.
   */
  function _get_available_variables($all = FALSE) {
    if (!isset($this->_variables)) {
      $this->_variables = array();
      $set = $this->get_set();
      $this_rule = $this->get_rule();

      //sort the rules
      _rules_sort_children($set['rules']);

      foreach (element_children($set['rules']) as $name) {
        if ($name == $this_rule['#name']) {
          if (!$all) {
            return $this->_variables;
          }
        }
        foreach ($set['rules'][$name]['#actions'] as $action) {
          $info = rules_get_element_info($action);
          $this->_variables += $info['new variables'];
        }
      }
    }
    return $this->_variables;
  }
}
