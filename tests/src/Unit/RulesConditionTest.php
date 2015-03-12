<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesConditionTest.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\rules\Plugin\RulesExpression\RulesCondition;


/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\RulesCondition
 * @group rules
 */
class RulesConditionTest extends RulesUnitTestBase {

  /**
   * The mocked condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The mocked data processor manager.
   *
   * @var \Drupal\rules\Engine\RulesDataProcessorManager
   */
  protected $processorManager;

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
    // use $this->trueCondition because it is a Rules expression, but we need a
    // condition plugin here.
    $this->trueCondition = $this->getMock('Drupal\rules\Core\RulesConditionInterface');
    $this->trueCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $this->trueCondition->expects($this->any())
      ->method('evaluate')
      ->will($this->returnValue(TRUE));

    $this->conditionManager = $this->getMockBuilder('Drupal\Core\Condition\ConditionManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->processorManager = $this->getMockBuilder('Drupal\rules\Engine\RulesDataProcessorManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->condition = new RulesCondition(['condition_id' => 'rules_or'], '', [], $this->conditionManager, $this->processorManager);
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

  /**
   * Tests that context values get data processed with processor mappings.
   */
  public function testDataProcessor() {
    $condition = new RulesCondition([
      'condition_id' => 'rules_or',
      'context_processors' => [
        'test' => [
          // We don't care about the data processor plugin name and
          // configuration since we will use a mock anyway.
          'plugin' => 'foo',
          'configuration' => [],
        ]
      ]
    ], '', [], $this->conditionManager, $this->processorManager);

    // Build some mocked context and definitions for our mock condition.
    $context = $this->getMock('Drupal\Core\Plugin\Context\ContextInterface');

    $condition->setContext('test', $context);

    $this->trueCondition->expects($this->exactly(2))
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $this->getMock('Drupal\Core\Plugin\Context\ContextDefinitionInterface')]));

    $this->trueCondition->expects($this->once())
      ->method('getProvidedDefinitions')
      ->will($this->returnValue([]));

    // Mock some original old value that will be replaced by the data processor.
    $this->trueCondition->expects($this->once())
      ->method('getContextValue')
      ->with('test')
      ->will($this->returnValue('old_value'));

    // The outcome of the data processor needs to get set on the condition.
    $this->trueCondition->expects($this->once())
      ->method('setContextValue')
      ->with('test', 'new_value');

    $this->conditionManager->expects($this->exactly(2))
      ->method('createInstance')
      ->will($this->returnValue($this->trueCondition));

    $data_processor = $this->getMock('Drupal\rules\Engine\RulesDataProcessorInterface');
    $data_processor->expects($this->once())
      ->method('process')
      ->with('old_value')
      ->will($this->returnValue('new_value'));

    $this->processorManager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($data_processor));

    $this->assertTrue($condition->evaluate());
  }

}
