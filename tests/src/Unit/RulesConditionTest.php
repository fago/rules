<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesConditionTest.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Plugin\Context\ContextDefinitionInterface;
use Drupal\Core\Plugin\Context\ContextInterface;
use Drupal\rules\Context\DataProcessorInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\DataProcessorManager;
use Drupal\rules\Plugin\RulesExpression\RulesCondition;
use Drupal\rules\Core\RulesConditionInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;


/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\RulesCondition
 * @group rules
 */
class RulesConditionTest extends UnitTestCase {

  /**
   * The mocked condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $conditionManager;

  /**
   * The mocked data processor manager.
   *
   * @var \Drupal\rules\Context\DataProcessorManager|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $processorManager;

  /**
   * The condition object being tested.
   *
   * @var \Drupal\rules\Plugin\RulesExpression\RulesCondition
   */
  protected $conditionExpression;

  /**
   * A condition plugin that always evaluates to TRUE.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $trueCondition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create a test condition plugin that always evaluates to TRUE.
    $this->trueCondition = $this->prophesize(RulesConditionInterface::class);
    $this->trueCondition->execute()->willReturn(TRUE);
    $this->trueCondition->evaluate()->willReturn(TRUE);

    $this->conditionManager = $this->prophesize(ConditionManager::class);

    $this->processorManager = $this->prophesize(DataProcessorManager::class);

    $this->conditionExpression = new RulesCondition(
      ['condition_id' => 'test_condition'], '', [],
      $this->conditionManager->reveal(), $this->processorManager->reveal());
  }

  /**
   * Tests that context definitions are retrieved form the plugin.
   */
  public function testContextDefinitions() {
    $context_definition = $this->prophesize(ContextDefinitionInterface::class);
    $this->trueCondition->getContextDefinitions()
      ->willReturn(['test' => $context_definition->reveal()])
      ->shouldBeCalledTimes(1);

    $this->conditionManager->createInstance('test_condition')
      ->willReturn($this->trueCondition->reveal())
      ->shouldBeCalledTimes(1);

    $this->assertSame($this->conditionExpression->getContextDefinitions(), ['test' => $context_definition->reveal()]);
  }

  /**
   * Tests that context values get data processed with processor mappings.
   */
  public function testDataProcessor() {
    $condition = new RulesCondition([
      'condition_id' => 'test_condition',
    ] + ContextConfig::create()
        // We don't care about the data processor plugin name and
        // configuration since we will use a mock anyway.
        ->process('test', 'foo', [])
        ->toArray(),
    '', [], $this->conditionManager->reveal(), $this->processorManager->reveal());

    // Build some mocked context and definitions for our mock condition.
    $context = $this->prophesize(ContextInterface::class);

    $condition->setContext('test', $context->reveal());

    $this->trueCondition->getContextDefinitions()->willReturn([
      'test' => $this->prophesize(ContextDefinitionInterface::class)->reveal(),
    ])->shouldBeCalledTimes(2);

    $this->trueCondition->getProvidedContextDefinitions()
      ->willReturn([])
      ->shouldBeCalledTimes(1);

    // Mock some original old value that will be replaced by the data processor.
    $this->trueCondition->getContextValue('test')
      ->willReturn('old_value')
      ->shouldBeCalledTimes(1);

    // The outcome of the data processor needs to get set on the condition.
    $this->trueCondition->setContextValue('test', 'new_value')->shouldBeCalledTimes(1);

    $this->trueCondition->refineContextDefinitions()->shouldBeCalledTimes(1);

    $this->conditionManager->createInstance('test_condition', ['negate' => FALSE])
      ->willReturn($this->trueCondition->reveal())
      ->shouldBeCalledTimes(1);
    $this->conditionManager->createInstance('test_condition')
      ->willReturn($this->trueCondition->reveal())
      ->shouldBeCalledTimes(1);

    $data_processor = $this->prophesize(DataProcessorInterface::class);
    $data_processor->process('old_value', Argument::any())
      ->willReturn('new_value')
      ->shouldBeCalledTimes(1);

    $this->processorManager->createInstance('foo', [])
      ->willReturn($data_processor->reveal())
      ->shouldBeCalledTimes(1);

    $this->assertTrue($condition->execute());
  }

  /**
   * Tests that negating a condition works.
   */
  public function testNegation() {
    $this->conditionManager->createInstance('test_condition', ['negate' => TRUE])
      ->willReturn($this->trueCondition->reveal())
      ->shouldBeCalledTimes(1);

    $this->conditionManager->createInstance('test_condition')
      ->willReturn($this->trueCondition->reveal())
      ->shouldBeCalledTimes(1);

    // Create a condition which is negated.
    $conditionExpression = new RulesCondition([
      'condition_id' => 'test_condition',
      'negate' => TRUE,
    ], '', [], $this->conditionManager->reveal(), $this->processorManager->reveal());

    $this->trueCondition->getContextDefinitions()->willReturn([]);
    $this->trueCondition->refineContextDefinitions()->shouldBeCalledTimes(1);
    $this->trueCondition->getProvidedContextDefinitions()
      ->willReturn([])
      ->shouldBeCalledTimes(1);

    $this->assertFalse($conditionExpression->execute());
  }

}
