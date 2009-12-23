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
 * Defines the node data wrapper.
 */
class RulesNodeDataWrapper extends RulesDataWrapper implements RulesDataSavableInterface {

  protected $itemName = 'node';

  /**
   * Implements RulesDataSavableInterface.
   */
  function save() {
    node_save($this->get());
  }

  /**
   * Implements RulesDataSavableInterface.
   */
  function load($nid) {
    $node = node_load($nid);
    if (empty($node)) {
      throw new RulesException('Unable to load content with id "@id"', array('@id' => $nid));
    }
    return $node;
  }

  /**
   * Implements RulesDataSavableInterface.
   */
  function getIdentifier() {
    $node = $this->get();
    if (empty($node->nid)) {
      throw new RulesException('Cannot get the identifier for unsaved content.');
    }
    return $node->nid;
  }
}

/**
 * Defines the user account data wrapper.
 */
class RulesUserDataWrapper extends RulesDataWrapper implements RulesDataSavableInterface {

  protected $itemName = 'user';

  /**
   * Implements RulesDataSavableInterface.
   */
  function save() {
    // Automatically save all field API and regular user fields.
    $table = drupal_get_schema('users');
    $user_fields = $table['fields'];
    $account = $this->get();
    list($id, $vid, $bundle) = entity_extract_ids('user', $account);
    $instances = field_info_instances('user', $bundle);
    // Extract the values of all valid keys into $edit.
    $edit = array_intersect_key((array)$account, $user_fields + $instances);
    user_save($account, $edit);
  }

  /**
   * Implements RulesDataSavableInterface.
   */
  function load($uid) {
    $user = user_load($uid);
    if (empty($user)) {
      throw new RulesException('Unable to load user account with id "@id"', array('@id' => $user));
    }
    return $user;
  }

  /**
   * Implements RulesDataSavableInterface.
   */
  function getIdentifier() {
    $user = $this->get();
    if (!isset($user->uid)) {
      throw new RulesException('Unable to retrieve the identifier for this user account.');
    }
    return $user->uid;
  }
}


/**
 * Action: Modify data.
 */
function rules_action_set($data, $value) {
  // Rules will use the data wrapper to update the value and if possible, save.
  return array('data' => $value);
}
