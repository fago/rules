<?php

namespace Drupal\Tests\rules\Unit;

use Drupal\rules\Core\ConditionManager;
use Drupal\Core\Plugin\Context\ContextDefinitionInterface;
use Drupal\rules\Context\DataProcessorInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\DataProcessorManager;
use Drupal\rules\Engine\ExecutionStateInterface;
use Drupal\rules\Plugin\RulesExpression\RulesCondition;
use Drupal\rules\Core\RulesConditionInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesExpression\RulesCondition
 * @group Rules
 */
class RulesConditionTest extends UnitTestCase {

  /**
   * The mocked condition manager.
   *
   * @var \Drupal\rules\Core\ConditionManager|\Prophecy\Prophecy\ProphecyInterface
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
   * Tests that context values get data processed with processor mappings.
   */
  public function testDataProcessor() {
    $this->conditionManager->createInstance('test_condition', ['negate' => FALSE])
      ->willReturn($this->trueCondition->reveal())
      ->shouldBeCalledTimes(1);

    $condition = new RulesCondition([
      'condition_id' => 'test_condition',
    ] + ContextConfig::create()
      // We don't care about the data processor plugin name and
      // configuration since we will use a mock anyway.
      ->process('test', 'foo', [])
      ->toArray(),
    '', [], $this->conditionManager->reveal(), $this->processorManager->reveal());

    $this->trueCondition->getContextDefinitions()->willReturn([
      'test' => $this->prophesize(ContextDefinitionInterface::class)->reveal(),
    ])->shouldBeCalledTimes(1);

    $this->trueCondition->getContextDefinition('test')->willReturn(
      $this->prophesize(ContextDefinitionInterface::class)->reveal()
    )->shouldBeCalledTimes(1);

    $this->trueCondition->getProvidedContextDefinitions()
      ->willReturn([])
      ->shouldBeCalledTimes(1);

    // Mock some original old value that will be replaced by the data processor.
    $this->trueCondition->getContextValue('test')
      ->willReturn('old_value')
      ->shouldBeCalled();

    // The outcome of the data processor needs to get set on the condition.
    $this->trueCondition->setContextValue('test', 'new_value')->shouldBeCalledTimes(1);
    $this->trueCondition->refineContextDefinitions([])->shouldBeCalledTimes(1);

    $data_processor = $this->prophesize(DataProcessorInterface::class);
    $data_processor->process('old_value', Argument::any())
      ->willReturn('new_value')
      ->shouldBeCalledTimes(1);

    $this->processorManager->createInstance('foo', [])
      ->willReturn($data_processor->reveal())
      ->shouldBeCalledTimes(1);

    // Build some mocked execution state.
    $state = $this->prophesize(ExecutionStateInterface::class);
    $prophecy = $state->getVariable('test');
    /** @var \Prophecy\Prophecy\MethodProphecy $prophecy */
    $prophecy->willReturn('old_value');

    $this->assertTrue($condition->executeWithState($state->reveal()));
  }

  /**
   * Tests that negating a condition works.
   */
  public function testNegation() {
    $this->trueCondition->getContextDefinitions()->willReturn([]);
    $this->trueCondition->refineContextDefinitions([])->shouldBeCalledTimes(1);
    $this->trueCondition->getProvidedContextDefinitions()
      ->willReturn([])
      ->shouldBeCalledTimes(1);

    $this->conditionManager->createInstance('test_condition', ['negate' => TRUE])
      ->willReturn($this->trueCondition->reveal())
      ->shouldBeCalledTimes(1);

    // Create a condition which is negated.
    $condition_expression = new RulesCondition([
      'condition_id' => 'test_condition',
      'negate' => TRUE,
    ], '', [], $this->conditionManager->reveal(), $this->processorManager->reveal());

    $this->assertFalse($condition_expression->execute());
  }

}
