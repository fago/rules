<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\ExpressionManagerInterface.
 */

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for the expression plugin manager.
 */
interface ExpressionManagerInterface extends PluginManagerInterface {

  /**
   * Creates a new rule.
   *
   * @param array $configuration
   *   The configuration array to create the plugin instance with.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\RuleInterface
   *   The created rule.
   */
  public function createRule(array $configuration = []);

  /**
   * Creates a new reaction rule.
   *
   * @param array $configuration
   *   The configuration array to create the plugin instance with.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\RuleInterface
   *   The created reaction rule.
   */
  public function createReactionRule(array $configuration = []);

  /**
   * Creates a new action expression.
   *
   * @param string $id
   *   The action plugin id.
   *
   * @return \Drupal\rules\Engine\ActionExpressionInterface
   *   The created action expression.
   */
  public function createAction($id);

  /**
   * Creates a new condition expression.
   *
   * @param string $id
   *   The condition plugin id.
   *
   * @return \Drupal\rules\Engine\ConditionExpressionInterface
   *   The created condition expression.
   */
  public function createCondition($id);

  /**
   * Creates a new 'and' condition container.
   *
   * @return \Drupal\rules\Engine\ConditionExpressionContainerInterface
   *   The created 'and' condition container.
   */
  public function createAnd();

  /**
   * Creates a new 'or' condition container.
   *
   * @return \Drupal\rules\Engine\ConditionExpressionContainerInterface
   *   The created 'or' condition container.
   */
  public function createOr();

}
