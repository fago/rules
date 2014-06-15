<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesConditionContainerInterface.
 */

namespace Drupal\rules\Engine;

/**
 * Defines a common interface for condition containers.
 */
interface RulesConditionContainerInterface extends RulesConditionInterface {

  /**
   * Adds a condition.
   *
   * @param \Drupal\rules\Engine\RulesConditionInterface $condition
   *   The condition object.
   *
   * @return $this
   */
  public function addCondition(RulesExpressionInterface $condition);

}
