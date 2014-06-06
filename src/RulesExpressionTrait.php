<?php

/**
 * @file
 * Contains \Drupal\rules\RulesExpressionTrait.
 */

namespace Drupal\rules;

use Drupal\rules\Plugin\RulesExpressionPluginManager;

/**
 * Provides convenience methods for building rules expressions.
 */
trait RulesExpressionTrait {

  /**
   * The rules expression manager.
   *
   * @var \Drupal\rules\Plugin\RulesExpressionPluginManager
   */
  protected $rulesExpressionManager;

  /**
   * Sets the rules expression manager instance.
   *
   * @return \Drupal\rules\Plugin\RulesExpressionPluginManager
   *   The rules expression manager.
   */
  public function getRulesExpressionManager() {
    return $this->rulesExpressionManager;
  }

  /**
   * Gets the rules expression manager instance.
   *
   * @param \Drupal\rules\Plugin\RulesExpressionPluginManager $expression_manager
   *   The rules expression manager.
   *
   * @return $this
   *   The current object for chaining.
   */
  public function setRulesExpressionManager(RulesExpressionPluginManager $expression_manager) {
    $this->rulesExpressionManager = $expression_manager;
    return $this;
  }

  /**
   * Creates a new Rules expression.
   *
   * @param string $id
   *   The expression plugin id.
   * @param array $configuration
   *   The configuration array to create the plugin instance with.
   *
   * @return \Drupal\rules\Engine\RulesExpressionInterface
   *   The created Rules expression.
   */
  protected function createRulesExpression($id, array $configuration = array()) {
    return $this->rulesExpressionManager->createInstance($id, $configuration);
  }

  /**
   * Creates a new rule.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\RuleInterface
   *   The created rule.
   */
  protected function createRulesRule() {
    return $this->createRulesExpression('rules_rule');
  }

  /**
   * Creates a new action.
   *
   * @param string $id
   *   The action plugin id.
   *
   * @return \Drupal\rules\Engine\RulesActionInterface;
   *   The created action.
   */
  protected function createRulesAction($id) {
    return $this->createRulesExpression('rules_action', array(
      'action_id' => $id,
    ));
  }

  /**
   * Creates a new condition.
   *
   * @param string $id
   *   The condition plugin id.
   *
   * @return \Drupal\rules\Engine\RulesConditionInterface
   *   The created condition.
   */
  protected function createRulesCondition($id) {
    return $this->createRulesExpression('rules_condition', array(
      'condition_id' => $id,
    ));
  }

  /**
   * Creates a new 'and' condition container.
   *
   * @return \Drupal\rules\Engine\RulesConditionContainerInterface
   *   The created 'and' condition container.
   */
  protected function createRulesAnd() {
    return $this->createRulesExpression('rules_and');
  }

  /**
   * Creates a new 'or' condition container.
   *
   * @return \Drupal\rules\Engine\RulesConditionContainerInterface
   *   The created 'or' condition container.
   */
  protected function createRulesOr() {
    return $this->createRulesExpression('rules_or');
  }

}
