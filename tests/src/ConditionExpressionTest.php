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
   * The mocked condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The mocked condition expression object.
   *
   * @var \Drupal\rules\Plugin\RulesExpression\ConditionExpression
   */
  protected $expression;

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->conditionManager = $this->getMockBuilder('Drupal\Core\Condition\ConditionManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->conditionManager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($this->trueCondition));

    $this->expression = $this->getMockConditionExpression(['getContext']);

    // Inject a mocked condition manager into the condition expression class.
    $property = new \ReflectionProperty($this->expression, 'conditionManager');
    $property->setAccessible(TRUE);
    $property->setValue($this->expression, $this->conditionManager);
  }

  /**
   * Tests that evaluate() correctly passes the context to the condition plugin.
   */
  public function testEvaluateWithContext() {
    // Build some mocked context and definitions for our mock condition
    // expression.
    $context_definition = $this->getMock('Drupal\rules\Context\ContextDefinitionInterface');

    $this->trueCondition->expects($this->once())
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $context_definition]));

    $context = $this->getMock('Drupal\rules\Context\ContextInterface');

    $this->trueCondition->expects($this->once())
      ->method('setContext')
      ->with('test', $context);

    $this->expression->expects($this->once())
      ->method('getContext')
      ->with('test')
      ->will($this->returnValue($context));

    $this->assertTrue($this->expression->evaluate());
  }

  /**
   * Tests that context definitions are retrieved form the plugin.
   */
  public function testContextDefinitions() {
    $context_definition = $this->getMock('Drupal\rules\Context\ContextDefinitionInterface');

    $this->trueCondition->expects($this->once())
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $context_definition]));

    $this->assertSame($this->expression->getContextDefinitions(), ['test' => $context_definition]);
  }

}
