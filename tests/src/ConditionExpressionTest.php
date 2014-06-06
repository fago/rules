<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\ConditionExpressionTest.
 */

namespace Drupal\rules\Tests;

/**
 * Tests the condition expression functionality.
 */
class ConditionExpressionTest extends RulesTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Condition Expression',
      'description' => 'Tests the ConditionExpression class',
      'group' => 'Rules',
    ];
  }

  /**
   * Tests that evaluate() correctly passes the context to the condition plugin.
   */
  public function testEvaluateWithContext() {
    // Build some mocked context and definitions for our mock condition
    // expression.
    $context_definition = $this->getMockBuilder('Drupal\rules\Context\ContextDefinitionInterface')
      ->getMock();

    $this->trueCondition->expects($this->once())
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $context_definition]));

    $this->trueCondition->expects($this->once())
      ->method('setContext')
      ->with('test');
    
    $conditionManager = $this->getMockBuilder('Drupal\Core\Condition\ConditionManager')
      ->disableOriginalConstructor()
      ->getMock();

    $conditionManager->expects($this->exactly(1))
      ->method('createInstance')
      ->will($this->returnValue($this->trueCondition));
    
    $expression = $this->getMockConditionExpression(['getContext']);

    // Inject a mocked condition manager into the condition expression class.
    $property = new \ReflectionProperty('Drupal\rules\Plugin\RulesExpression\ConditionExpression', 'conditionManager');
    $property->setAccessible(TRUE);
    $property->setValue($expression, $conditionManager);

    $context = $this->getMockBuilder('Drupal\rules\Context\ContextInterface')
      ->getMock();

    $expression->expects($this->once())
      ->method('getContext')
      ->with('test')
      ->will($this->returnValue($context));

    $this->assertTrue($expression->evaluate());
  }

}
