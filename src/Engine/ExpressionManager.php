<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\ExpressionManager.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\rules\Annotation\RulesExpression;

/**
 * Plugin manager for all Rules expressions.
 *
 * @see \Drupal\rules\Engine\ExpressionInterface
 */
class ExpressionManager extends DefaultPluginManager implements ExpressionManagerInterface {

  /**
   * A map from class names to plugin ids.
   *
   * @var string[]
   */
  protected $classNamePluginIdMap;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler, $plugin_definition_annotation_name = RulesExpression::class) {
    $this->alterInfo('rules_expression');
    parent::__construct('Plugin/RulesExpression', $namespaces, $module_handler, ExpressionInterface::class, $plugin_definition_annotation_name);
  }

  /**
   * {@inheritdoc}
   */
  public function createRule(array $configuration = []) {
    return $this->createInstance('rules_rule', $configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function createAction($id) {
    return $this->createInstance('rules_action', [
      'action_id' => $id,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function createCondition($id) {
    return $this->createInstance('rules_condition', [
      'condition_id' => $id,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function createAnd() {
    return $this->createInstance('rules_and');
  }

  /**
   * {@inheritdoc}
   */
  public function createOr() {
    return $this->createInstance('rules_or');
  }

}
