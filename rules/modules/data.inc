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
   * Implementation of RulesDataSavableInterface.
   */
  function save() {
    node_save($this->get());
  }

  /**
   * Implementation of RulesDataSavableInterface.
   */
  function load($nid) {
    $node = node_load($nid);
    if (empty($node)) {
      throw new RulesException('Unable to load content with id "@id"', array('@id' => $nid));
    }
    return $node;
  }

  /**
   * Implementation of RulesDataSavableInterface.
   */
  function getIdentifier() {
    $node = $this->get();
    if (empty($node->nid)) {
      throw new RulesException('Cannot get the identifier for unsaved content.');
    }
    return $node->nid;
  }
}
