<?php

namespace Drupal\rules\Engine;

use Drupal\rules\Context\ContextConfig;

/**
 * Contains condition expressions.
 */
interface ConditionExpressionContainerInterface extends ConditionExpressionInterface, ExpressionContainerInterface {

  /**
   * Creates a condition expression and adds it to the container.
   *
   * @param string $condition_id
   *   The condition plugin id.
   * @param \Drupal\rules\Context\ContextConfig $config
   *   (optional) The configuration for the specified plugin.
   *
   * @return \Drupal\rules\Core\RulesConditionInterface
   *   The created condition.
   */
  public function addCondition($condition_id, ContextConfig $config = NULL);

}
