<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesDrupalTestBase.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\RulesExpressionTrait;
use Drupal\simpletest\KernelTestBase;

/**
 * Base class for Rules Drupal unit tests.
 */
abstract class RulesDrupalTestBase extends KernelTestBase {

  use RulesExpressionTrait;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

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
    $condition = $this->rulesExpressionManager->createInstance('rules_condition', array(
      'condition_id' => $id,
    ));
    return $condition;
  }

}
