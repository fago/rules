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
   * The rules expression plugin manager.
   *
   * @var \Drupal\rules\Plugin\RulesExpressionPluginManager
   */
  protected $rulesExpressionManager;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

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
    $this->typedDataManager = $this->container->get('typed_data_manager');
  }

  /**
   * Creates a new condition.
   *
   * @param array $configuration
   *   The configuration array to create the plugin instance with.
   *
   * @return \Drupal\rules\Engine\RulesConditionInterface
   */
  protected function createCondition(array $configuration) {
    return $this->rulesExpressionManager->createInstance('rules_condition', $configuration);
  }

}
