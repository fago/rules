<?php

/**
 * @file
 * Contains Drupal\rules\Tests\RulesEngineTest.
 */

namespace Drupal\rules\Tests;

use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests the core rules engine functionality.
 */
class RulesEngineTest extends DrupalUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('rules', 'rules_test', 'system');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Rules Engine tests',
      'description' => 'Test using the rules API to create and evaluate rules.',
      'group' => 'Rules',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->rulesManager = $this->container->get('plugin.manager.rules');
    $this->conditionManager = $this->container->get('plugin.manager.condition');
    $this->actionManager = $this->container->get('plugin.manager.action');
  }

  /**
   * Tests creating a rule and iterating over the rule elements.
   */
  public function testRuleCreation() {
    $rule = $this->rulesManager->createInstance('rules_rule');
    $true_condition = $this->conditionManager->createInstance('rules_test_condition_true');
    $false_condition = $this->conditionManager->createInstance('rules_test_condition_false');
    $negated_condition = $this->conditionManager->createInstance('rules_test_condition_true')->negate();
    $or = $this->rulesManager->createInstance('rules_or');
    $and = $this->rulesManager->createInstance('rules_and');
    $action = $this->actionManager->createInstance('rules_test_action');
    $rule->condition($true_condition)
      ->condition($true_condition)
      ->condition($or
        ->condition($negated_condition)
        ->condition($false_condition)
        ->condition($and
          ->condition($false_condition)
          ->condition($negated_condition)));
    $rule->action($action);
    $rule->execute();
  }
}
