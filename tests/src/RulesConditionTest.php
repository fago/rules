<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesConditionTest.
 */

namespace Drupal\rules\Tests;

/**
 * Tests the Rules condition functionality.
 */
class RulesConditionTest extends RulesTestBase {

  /**
   * The mocked condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The mocked condition object.
   *
   * @var \Drupal\rules\Plugin\RulesExpression\RulesCondition
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Rules Condition',
      'description' => 'Tests the RulesCondition class',
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

    $this->condition = $this->getMockConditionExpression(['getContext']);

    // Inject a mocked condition manager into the condition class.
    $property = new \ReflectionProperty($this->condition, 'conditionManager');
    $property->setAccessible(TRUE);
    $property->setValue($this->condition, $this->conditionManager);
  }

  /**
   * Tests that evaluate() correctly passes the context to the condition plugin.
   */
  public function testEvaluateWithContext() {
    // Build some mocked context and definitions for our mock condition.
    $context_definition = $this->getMock('Drupal\rules\Context\ContextDefinitionInterface');

    $this->trueCondition->expects($this->once())
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $context_definition]));

    $context = $this->getMock('Drupal\rules\Context\ContextInterface');

    $this->trueCondition->expects($this->once())
      ->method('setContext')
      ->with('test', $context);

    $this->condition->expects($this->once())
      ->method('getContext')
      ->with('test')
      ->will($this->returnValue($context));

    $this->assertTrue($this->condition->evaluate());
  }

  /**
   * Tests that context definitions are retrieved form the plugin.
   */
  public function testContextDefinitions() {
    $context_definition = $this->getMock('Drupal\rules\Context\ContextDefinitionInterface');

    $this->trueCondition->expects($this->once())
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $context_definition]));

    $this->assertSame($this->condition->getContextDefinitions(), ['test' => $context_definition]);
  }

}
