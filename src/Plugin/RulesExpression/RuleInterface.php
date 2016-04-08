<?php

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Engine\ActionExpressionContainerInterface;
use Drupal\rules\Engine\ConditionExpressionContainerInterface;
use Drupal\rules\Engine\ActionExpressionInterface;
use Drupal\rules\Engine\ExpressionContainerInterface;

/**
 * Defines a rule.
 */
interface RuleInterface extends ExpressionContainerInterface, ActionExpressionInterface {

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

  /**
   * Returns the conditions container of this rule.
   *
   * @return \Drupal\rules\Engine\ConditionExpressionContainerInterface
   *   The condition container of this rule.
   */
  public function getConditions();

  /**
   * Sets the condition container.
   *
   * @param \Drupal\rules\Engine\ConditionExpressionContainerInterface $conditions
   *   The condition container to set.
   *
   * @return $this
   */
  public function setConditions(ConditionExpressionContainerInterface $conditions);

  /**
   * Creates an action expression and adds it to the container.
   *
   * @param string $action_id
   *   The action plugin id.
   * @param \Drupal\rules\Context\ContextConfig $config
   *   (optional) The configuration for the specified plugin.
   *
   * @return $this
   */
  public function addAction($action_id, ContextConfig $config = NULL);

  /**
   * Returns the actions of this rule.
   *
   * @return \Drupal\rules\Engine\ActionExpressionContainerInterface
   *   The action container of this rule.
   */
  public function getActions();

  /**
   * Sets the action container.
   *
   * @param \Drupal\rules\Engine\ActionExpressionContainerInterface $actions
   *   The action container to set.
   *
   * @return $this
   */
  public function setActions(ActionExpressionContainerInterface $actions);

}
