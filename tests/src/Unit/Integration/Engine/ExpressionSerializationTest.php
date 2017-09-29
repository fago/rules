<?php

namespace Drupal\Tests\rules\Unit\Integration\Engine;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * Tests serializing expression objects.
 *
 * @group Rules
 */
class ExpressionSerializationTest extends RulesIntegrationTestBase {

  /**
   * Tests serializing action expressions.
   */
  public function testActionExpressionSerialization() {
    $action = $this->rulesExpressionManager
      ->createAction('rules_test_string', ContextConfig::create()
        ->setValue('text', 'test')->toArray()
      );
    $serialized_expression = serialize($action);
    $action = unserialize($serialized_expression);
    $result = RulesComponent::create($action)
      ->provideContext('concatenated')
      ->execute();
    $this->assertSame('testtest', $result['concatenated']);
  }

  /**
   * Tests serializing condition expressions.
   */
  public function testConditionExpressionSerialization() {
    $condition = $this->rulesExpressionManager
      ->createCondition('rules_test_false', []);
    $serialized_expression = serialize($condition);
    $condition = unserialize($serialized_expression);
    $result = $condition->execute();
    $this->assertFalse($result);
  }

  /**
   * Tests condition container base class serialization.
   */
  public function testConditionContainerExpressionSerialization() {
    $expression = $this->rulesExpressionManager
      ->createAnd();
    $expression->addCondition('rules_test_false');
    $serialized_expression = serialize($expression);
    $expression = unserialize($serialized_expression);
    $result = $expression->execute();
    $this->assertFalse($result);
  }

  /**
   * Tests action container base class serialization.
   */
  public function testActionContainerExpressionSerialization() {
    $expression = $this->rulesExpressionManager
      ->createInstance('rules_action_set');
    $expression->addAction('rules_test_string', ContextConfig::create()
      ->setValue('text', 'test'));
    $serialized_expression = serialize($expression);
    $expression = unserialize($serialized_expression);
    $result = RulesComponent::create($expression)
      ->provideContext('concatenated')
      ->execute();
    $this->assertSame('testtest', $result['concatenated']);
  }

  /**
   * Tests rule serialization.
   */
  public function testRuleExpressionSerialization() {
    $expression = $this->rulesExpressionManager
      ->createRule();
    $expression->addAction('rules_test_string', ContextConfig::create()
      ->setValue('text', 'test'));
    $condition = $this->rulesExpressionManager
      ->createCondition('rules_test_false', []);
    $condition->negate(TRUE);
    $expression->addExpressionObject($condition);

    $serialized_expression = serialize($expression);
    $expression = unserialize($serialized_expression);
    $result = RulesComponent::create($expression)
      ->provideContext('concatenated')
      ->execute();
    $this->assertSame('testtest', $result['concatenated']);
  }

}
