<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesDrupalTestBase.
 */

namespace Drupal\rules\Tests;

use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Action\ActionManager;
use Drupal\Core\Condition\ConditionManager;
use Drupal\rules\Annotation\RulesExpression;
use Drupal\rules\Engine\RulesExpressionInterface;
use Drupal\rules\Plugin\RulesExpression\Rule;
use Drupal\rules\Plugin\RulesExpressionPluginManager;
use Drupal\simpletest\KernelTestBase;

/**
 * Base class for Rules Drupal unit tests.
 */
abstract class RulesDrupalTestBase extends KernelTestBase {

  /**
   * The rules expression plugin manager.
   *
   * @var \Drupal\rules\Plugin\RulesExpressionPluginManager
   */
  protected $rulesExpressionManager;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The rules action plugin manager.
   *
   * @var \Drupal\Core\Action\ActionManager
   */
  protected $actionManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules', 'rules_test', 'system'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->rulesExpressionManager = $this->container->get('plugin.manager.rules_expression');
    $this->conditionManager = $this->container->get('plugin.manager.condition');
    $this->actionManager = $this->container->get('plugin.manager.action');
  }

  /**
   * Creates a new rule.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\Rule
   */
  protected function createRule() {
    return $this->rulesExpressionManager->createInstance('rules_rule');
  }

  /**
   * Creates a new Rules expression.
   *
   * @param string $id
   *   The expression plugin id.
   *
   * @return \Drupal\rules\Engine\RulesExpressionInterface
   */
  protected function createExpression($id) {
    return $this->rulesExpressionManager->createInstance($id);
  }

  /**
   * Creates a new action.
   *
   * @param string $id
   *   The action plugin id.
   *
   * @return \Drupal\Core\Action\ActionInterface
   */
  protected function createAction($id) {
    $actionExpression = $this->rulesExpressionManager->createInstance('rules_action', array(
      'action_id' => $id,
    ));
    return $actionExpression;
  }

  /**
   * Creates a new condition.
   *
   * @param string $id
   *   The condition plugin id.
   *
   * @return \Drupal\rules\Engine\RulesConditionInterface
   */
  protected function createCondition($id) {
    return $this->conditionManager->createInstance($id);
  }

}
