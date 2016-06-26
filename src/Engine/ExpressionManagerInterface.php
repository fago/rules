<?php

namespace Drupal\rules\Engine;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for the expression plugin manager.
 */
interface ExpressionManagerInterface extends PluginManagerInterface {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\rules\Engine\ExpressionInterface
   *   A fully configured plugin instance.
   */
  public function createInstance($plugin_id, array $configuration = []);

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
   * Creates a new action set.
   *
   * @param array $configuration
   *   The configuration array to create the plugin instance with.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\ActionSet
   *   The created action set.
   */
  public function createActionSet(array $configuration = []);

  /**
   * Creates a new action expression.
   *
   * @param string $id
   *   The action plugin id.
   * @param array $configuration
   *   Optional configuration settings.
   *
   * @return \Drupal\rules\Engine\ActionExpressionInterface
   *   The created action expression.
   */
  public function createAction($id, array $configuration = []);

  /**
   * Creates a new condition expression.
   *
   * @param string $id
   *   The condition plugin id.
   * @param array $configuration
   *   Optional configuration settings.
   *
   * @return \Drupal\rules\Engine\ConditionExpressionInterface
   *   The created condition expression.
   */
  public function createCondition($id, array $configuration = []);

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
