<?php

/**
 * @file
 * Contains \Drupal\rules\RuleInterface.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Core\Action\ActionInterface;
use Drupal\rules\Engine\RulesConditionContainerInterface;
use Drupal\rules\Engine\RulesConditionInterface;

/**
 * Defines an interface for rules.
 */
interface RuleInterface extends ActionInterface {

  /**
   * Adds a condition.
   *
   * @param \Drupal\rules\Engine\RulesConditionInterface $condition
   *   The condition object.
   *
   * @return $this
   *   The current rule object for chaining.
   */
  public function addCondition(RulesConditionInterface $condition);

  /**
   * Returns the conditions of this rule.
   *
   * @return \Drupal\rules\Engine\RulesConditionInterface[]
   *   The conditions of this rule.
   */
  public function getConditions();

  /**
   * Sets the condition container.
   *
   * @param \Drupal\rules\Engine\RulesConditionContainerInterface $conditions
   *   The condition container to set.
   *
   * @return $this
   *   The current rule object for chaining.
   */
  public function setConditions(RulesConditionContainerInterface $conditions);

  /**
   * Adds an action.
   *
   * @param \Drupal\Core\Action\ActionInterface $action
   *   The action object to add.
   *
   * @return $this
   *   The current rule object for chaining.
   */
  public function addAction(ActionInterface $action);

  /**
   * Returns the actions of this rule.
   *
   * @return \Drupal\Core\Action\ActionInterface[]
   *   The actions of this rule.
   */
  public function getActions();

}
