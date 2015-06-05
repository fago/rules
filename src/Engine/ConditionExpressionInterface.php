<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ConditionExpressionInterface.
 */

namespace Drupal\rules\Engine;

/**
 * Defines the interface for Rules expressions that can be used as conditions.
 */
interface ConditionExpressionInterface extends ExpressionInterface {

  /**
   * Determines whether condition result will be negated.
   *
   * @return bool
   *   Whether the condition result will be negated.
   */
  public function isNegated();

}
