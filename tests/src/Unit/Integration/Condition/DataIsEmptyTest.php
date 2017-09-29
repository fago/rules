<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\DataIsEmpty
 * @group RulesCondition
 */
class DataIsEmptyTest extends RulesIntegrationTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface
   */
  protected $condition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->condition = $this->conditionManager->createInstance('rules_data_is_empty');
  }

  /**
   * Tests evaluating the condition.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluation() {
    // Test a ComplexDataInterface object.
    $entity_adapter_empty = $this->prophesize(ComplexDataInterface::class);
    $entity_adapter_empty->isEmpty()->willReturn(TRUE)->shouldBeCalledTimes(1);

    $context = $this->condition->getContext('data');
    $context = Context::createFromContext($context, $entity_adapter_empty->reveal());
    $this->condition->setContext('data', $context);
    $this->assertTrue($this->condition->evaluate());

    $entity_adapter_full = $this->prophesize(ComplexDataInterface::class);
    $entity_adapter_full->isEmpty()->willReturn(FALSE)->shouldBeCalledTimes(1);

    $context = Context::createFromContext($context, $entity_adapter_full->reveal());
    $this->condition->setContext('data', $context);
    $this->assertFalse($this->condition->evaluate());

    // These should all return FALSE.
    // A non-empty array.
    $context = Context::createFromContext(
      $context,
      $this->getTypedData('list', [1, 2, 3])
    );
    $this->condition->setContext('data', $context);
    $this->assertFalse($this->condition->evaluate());

    // An array containing an empty list.
    $context = Context::createFromContext($context, $this->getTypedData('list', [[]]));
    $this->condition->setContext('data', $context);
    $this->assertFalse($this->condition->evaluate());

    // An array with a zero-value element.
    $context = Context::createFromContext($context, $this->getTypedData('list', [0]));
    $this->condition->setContext('data', $context);
    $this->assertFalse($this->condition->evaluate());

    // A scalar value.
    $context = Context::createFromContext($context, $this->getTypedData('integer', 1));
    $this->condition->setContext('data', $context);
    $this->assertFalse($this->condition->evaluate());

    $context = Context::createFromContext($context, $this->getTypedData('string', 'short string'));
    $this->condition->setContext('data', $context);
    $this->assertFalse($this->condition->evaluate());

    // These should all return TRUE.
    // An empty array.
    $context = Context::createFromContext($context, $this->getTypedData('list', []));
    $this->condition->setContext('data', $context);
    $this->assertTrue($this->condition->evaluate());

    // The false/zero/NULL values.
    $context = Context::createFromContext($context, $this->getTypedData('boolean', FALSE));
    $this->condition->setContext('data', $context);
    $this->assertTrue($this->condition->evaluate());

    $context = Context::createFromContext($context, $this->getTypedData('integer', 0));
    $this->condition->setContext('data', $context);
    $this->assertTrue($this->condition->evaluate());

    $context = Context::createFromContext($context, $this->getTypedData('string', NULL));
    $this->condition->setContext('data', $context);
    $this->assertTrue($this->condition->evaluate());

    // An empty string.
    $context = Context::createFromContext($context, $this->getTypedData('string', ''));
    $this->condition->setContext('data', $context);
    $this->assertTrue($this->condition->evaluate());
  }

}
