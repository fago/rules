<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesUnitTestBase.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\rules\Engine\ActionExpressionInterface;
use Drupal\rules\Engine\ConditionExpressionInterface;
use Drupal\rules\Engine\RulesStateInterface;
use Drupal\rules\Engine\ExpressionPluginManagerInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Helper class with mock objects.
 */
abstract class RulesUnitTestBase extends UnitTestCase {

  /**
   * A mocked condition that always evaluates to TRUE.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $trueConditionExpression;

  /**
   * A mocked condition that always evaluates to FALSE.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $falseConditionExpression;

  /**
   * A mocked dummy action object.
   *
   * @var \Drupal\rules\Engine\ActionExpressionInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $testActionExpression;

  /**
   * The mocked expression manager object.
   *
   * @var \Drupal\rules\Engine\ExpressionPluginManager|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $expressionManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->trueConditionExpression = $this->prophesize(ConditionExpressionInterface::class);

    $this->trueConditionExpression->execute()->willReturn(TRUE);
    $this->trueConditionExpression->executeWithState(
      Argument::type(RulesStateInterface::class))->willReturn(TRUE);

    $this->falseConditionExpression = $this->prophesize(ConditionExpressionInterface::class);
    $this->falseConditionExpression->execute()->willReturn(FALSE);
    $this->falseConditionExpression->executeWithState(
      Argument::type(RulesStateInterface::class))->willReturn(FALSE);

    $this->testActionExpression = $this->prophesize(ActionExpressionInterface::class);

    $this->expressionManager = $this->prophesize(ExpressionPluginManagerInterface::class);
  }

}
