<?php

/**
 * @file
 * Contains \Drupal\rules\RuleInterface.
 */

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\rules\Engine\RulesActionContainerInterface;
use Drupal\rules\Engine\RulesConditionContainerInterface;
use Drupal\rules\Engine\RulesConditionInterface;

/**
 * Defines a rule.
 */
interface RuleInterface extends RulesActionContainerInterface {

  /**
   * Adds a condition.
   *
   * @param \Drupal\rules\Engine\RulesConditionInterface $condition
   *   The condition object.
   *
   * @return $this
   */
  public function addCondition(RulesConditionInterface $condition);

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
