<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesConditionTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Plugin\RulesExpression\RulesCondition;


/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\RulesCondition
 * @group rules
 */
class RulesConditionTest extends RulesTestBase {

  /**
   * The mocked condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The condition object being tested.
   *
   * @var \Drupal\rules\Plugin\RulesExpression\RulesCondition
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create a test condition plugin that always evaluates to TRUE. We cannot
    // use $this->trueCondtion because it is a Rules expression, but we need a
    // condition plugin here.
    $this->trueCondition = $this->getMock('Drupal\rules\Engine\RulesConditionInterface');
    $this->trueCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $this->trueCondition->expects($this->any())
      ->method('evaluate')
      ->will($this->returnValue(TRUE));

    $this->conditionManager = $this->getMockBuilder('Drupal\Core\Condition\ConditionManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->condition = new RulesCondition(['condition_id' => 'rules_or'], '', [], $this->conditionManager);
  }

  /**
   * Tests that evaluate() correctly passes the context to the condition plugin.
   */
  public function testEvaluateWithContext() {
    // Build some mocked context and definitions for our mock condition.
    $context = $this->getMock('Drupal\Core\Plugin\Context\ContextInterface');
    $context->expects($this->once())
      ->method('getContextValue')
      ->will($this->returnValue('value'));

    $this->condition->setContext('test', $context);

    $this->trueCondition->expects($this->exactly(2))
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $this->getMock('Drupal\Core\Plugin\Context\ContextDefinitionInterface')]));

    // Make sure that the context value is set as expected.
    $this->trueCondition->expects($this->once())
      ->method('setContextValue')
      ->with('test', 'value');

    $this->trueCondition->expects($this->once())
      ->method('getProvidedDefinitions')
      ->will($this->returnValue([]));

    $this->conditionManager->expects($this->exactly(2))
      ->method('createInstance')
      ->will($this->returnValue($this->trueCondition));

    $this->assertTrue($this->condition->evaluate());
  }

  /**
   * Tests that context definitions are retrieved form the plugin.
   */
  public function testContextDefinitions() {
    $context_definition = $this->getMock('Drupal\Core\Plugin\Context\ContextDefinitionInterface');
    $this->trueCondition->expects($this->once())
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $context_definition]));

    $this->conditionManager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($this->trueCondition));

    $this->assertSame($this->condition->getContextDefinitions(), ['test' => $context_definition]);
  }

}
