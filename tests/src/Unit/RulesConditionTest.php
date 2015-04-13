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
   * @var \Drupal\rules\Context\DataProcessorManager
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
    $this->trueConditionExpression = $this->getMock('Drupal\rules\Core\RulesConditionInterface');
    $this->trueConditionExpression->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $this->trueConditionExpression->expects($this->any())
      ->method('evaluate')
      ->will($this->returnValue(TRUE));

    $this->conditionManager = $this->getMockBuilder('Drupal\Core\Condition\ConditionManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->processorManager = $this->getMockBuilder('Drupal\rules\Context\DataProcessorManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->condition = new RulesCondition(['condition_id' => 'rules_or'], '', [], $this->conditionManager, $this->processorManager);
  }

  /**
   * Tests that context definitions are retrieved form the plugin.
   */
  public function testContextDefinitions() {
    $context_definition = $this->getMock('Drupal\Core\Plugin\Context\ContextDefinitionInterface');
    $this->trueConditionExpression->expects($this->once())
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $context_definition]));

    $this->conditionManager->expects($this->once())
      ->method('createInstance')
      ->will($this->returnValue($this->trueConditionExpression));

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

    $this->trueConditionExpression->expects($this->exactly(2))
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $this->getMock('Drupal\Core\Plugin\Context\ContextDefinitionInterface')]));

    $this->trueConditionExpression->expects($this->once())
      ->method('getProvidedContextDefinitions')
      ->will($this->returnValue([]));

    // Mock some original old value that will be replaced by the data processor.
    $this->trueConditionExpression->expects($this->once())
      ->method('getContextValue')
      ->with('test')
      ->will($this->returnValue('old_value'));

    // The outcome of the data processor needs to get set on the condition.
    $this->trueConditionExpression->expects($this->once())
      ->method('setContextValue')
      ->with('test', 'new_value');

    $this->conditionManager->expects($this->exactly(2))
      ->method('createInstance')
      ->will($this->returnValue($this->trueConditionExpression));

    $data_processor = $this->getMock('Drupal\rules\Context\DataProcessorInterface');
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
