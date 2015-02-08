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
   * @param string $plugin_id
   *   The id of the expression plugin to add.
   * @param array $configuration
   *   (optional) The configuration for the specified plugin.
   *
   * @throws \Drupal\rules\Exception\InvalidExpressionException
   *   Thrown if the wrong expression is passed; e.g. if a condition expression
   *   is added to an action expression container.
   *
   * @return $this
   */
  public function addExpression($plugin_id, $configuration = NULL);

  /**
   * Adds an expression object.
   *
   * @param \Drupal\rules\Engine\RulesExpressionInterface $expression
   *   The expression object.
   *
   * @throws \Drupal\rules\Exception\InvalidExpressionException
   *   Thrown if the wrong expression is passed; e.g. if a condition expression
   *   is added to an action expression container.
   *
   * @return $this
   */
  public function addExpressionObject(RulesExpressionInterface $expression);

}
