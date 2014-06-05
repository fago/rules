<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesActionContainerInterface.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Action\ActionInterface;

/**
 * Defines a common interface for action containers.
 */
interface RulesActionContainerInterface extends ActionInterface {

  /**
   * Adds a action.
   *
   * @param \Drupal\Core\Action\ActionInterface; $action
   *   The action object.
   *
   * @return $this
   *   The current action container object for chaining.
   */
  public function addAction(ActionInterface $action);

}
