<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesConditionContainerInterface.
 */

namespace Drupal\rules\Engine;

/**
 * Contains condition expressions.
 */
interface RulesConditionContainerInterface extends RulesExpressionContainerInterface {

  /**
   * Creates a condition expression and adds it to the container.
   *
   * @param string $condition_id
   *   The condition plugin id.
   *
   * @return \Drupal\rules\Engine\RulesConditionInterface
   *   The created condition.
   */
  public function addCondition($condition_id, $configuration = NULL);
}
