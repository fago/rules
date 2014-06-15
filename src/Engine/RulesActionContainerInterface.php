<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesActionContainerInterface.
 */

namespace Drupal\rules\Engine;

/**
 * Defines a common interface for action containers.
 */
interface RulesActionContainerInterface extends RulesExpressionInterface {

  /**
   * Adds an action.
   *
   * @param \Drupal\rules\Engine\RulesExpressionInterface $action
   *   The action object.
   *
   * @return $this
   */
  public function addAction(RulesExpressionInterface $action);

}
