<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesActionContainerInterface.
 */

namespace Drupal\rules\Engine;

/**
 * Defines a common interface for action containers.
 */
interface RulesActionContainerInterface extends RulesActionInterface {

  /**
   * Adds a action.
   *
   * @param \Drupal\rules\Engine\RulesActionInterface $action
   *   The action object.
   *
   * @return $this
   *   The current action container object for chaining.
   */
  public function addAction(RulesActionInterface $action);

}
