<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesExpressionContainerInterface.
 */

namespace Drupal\rules\Engine;

/**
 * Defines a common interface for expressions containing other expressions.
 *
 * Usually expression containers also implement the
 * RulesActionContainerInterface or RulesConditionContainerInterface in order
 * to denote whether it contains action or condition expressions.
 */
interface RulesExpressionContainerInterface extends RulesExpressionInterface {

  /**
   * Creates and adds an expression.
   *
   * @param \Drupal\rules\Engine\RulesExpressionInterface $expression
   *   The expression object.
   *
   * @throws \Drupal\rules\Expression\InvalidExpressionException
   *   Thrown if the wrong expression is passed; e.g. if a condition expression
   *   is added to an action expression container.
   *
   * @return $this
   */
  public function addExpression($plugin_id, $configuration);

  /**
   * Adds an expression object.
   *
   * @param \Drupal\rules\Engine\RulesExpressionInterface $expression
   *   The expression object.
   *
   * @throws \Drupal\rules\Expression\InvalidExpressionException
   *   Thrown if the wrong expression is passed; e.g. if a condition expression
   *   is added to an action expression container.
   *
   * @return $this
   */
  public function addExpressionObject(\Drupal\rules\Engine\RulesExpressionInterface $expression);

}
