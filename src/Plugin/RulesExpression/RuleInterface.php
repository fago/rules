<?php

/**
 * @file
 * Contains \Drupal\rules\RuleInterface.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Core\Action\ActionInterface;
use Drupal\rules\Engine\RulesConditionInterface;
use Drupal\rules\Plugin\RulesExpression\Rule;

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
