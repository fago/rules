<?php

/**
 * @file
 * Contains \Drupal\rules\RuleInterface.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\RulesActionContainerInterface;
use Drupal\rules\Engine\RulesConditionContainerInterface;
use Drupal\rules\Engine\RulesExpressionActionInterface;
use Drupal\rules\Engine\RulesExpressionContainerInterface;

/**
 * Defines a rule.
 */
interface RuleInterface extends RulesExpressionContainerInterface, RulesExpressionActionInterface {

  /**
   * Creates a condition expression and adds it to the container.
   *
   * @param string $condition_id
   *   The condition plugin id.
   * @param array $configuration
   *   (optional) The configuration for the specified plugin.
   *
   * @return \Drupal\rules\Core\RulesConditionInterface
   *   The created condition.
   */
  public function addCondition($condition_id, $configuration = NULL);

  /**
   * Returns the conditions container of this rule.
   *
   * @return \Drupal\rules\Engine\RulesConditionContainerInterface
   *   The condition container of this rule.
   */
  public function getConditions();

  /**
   * Sets the condition container.
   *
   * @param \Drupal\rules\Engine\RulesConditionContainerInterface $conditions
   *   The condition container to set.
   *
   * @return $this
   */
  public function setConditions(RulesConditionContainerInterface $conditions);

  /**
   * Creates an action expression and adds it to the container.
   *
   * @param string $action_id
   *   The action plugin id.
   * @param array $configuration
   *   (optional) The configuration for the specified plugin.
   *
   * @return $this
   */
  public function addAction($action_id, $configuration = NULL);

  /**
   * Returns the actions of this rule.
   *
   * @return \Drupal\rules\Engine\RulesActionContainerInterface
   *   The action container of this rule.
   */
  public function getActions();

  /**
   * Sets the action container.
   *
   * @param \Drupal\rules\Engine\RulesActionContainerInterface $actions
   *   The action container to set.
   *
   * @return $this
   */
  public function setActions(RulesActionContainerInterface $actions);

}
