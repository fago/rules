<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesTestBase.
 */

namespace Drupal\rules\Tests;

use Drupal\Tests\UnitTestCase;

/**
 * Helper class with mock objects.
 */
abstract class RulesTestBase extends UnitTestCase {

  /**
   * A mocked condition that always evaluates to TRUE.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $trueCondition;

  /**
   * A mocked condition that always evaluates to FALSE.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $falseCondition;

  /**
   * A mocked dummy action object.
   *
   * @var \Drupal\Core\Action\ActionInterface
   */
  protected $testAction;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->trueCondition = $this->getMockBuilder('Drupal\rules\Engine\RulesConditionInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->trueCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $this->falseCondition = $this->getMockBuilder('Drupal\rules\Engine\RulesConditionInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->falseCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));

    $this->testAction = $this->getMockBuilder('Drupal\Core\Action\ActionBase')
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Creates an 'and' condition container with the basic plugin methods mocked.
   *
   * @param array $methods
   *   (optional) The methods to mock.
   *
   * @return \Drupal\rules\Engine\RulesConditionContainerInterface
   *   The mocked 'and' condition container.
   */
  public function getMockAnd(array $methods = []) {
    $methods += ['getPluginId', 'getBasePluginId', 'getDerivativeId', 'getPluginDefinition'];

    $rule = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpression\RulesAnd')
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();

    $rule->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('rules_and'));

    $rule->expects($this->any())
      ->method('getBasePluginId')
      ->will($this->returnValue('rules_and'));

    $rule->expects($this->any())
      ->method('getDerivativeId')
      ->will($this->returnValue(NULL));

    $rule->expects($this->any())
      ->method('getPluginDefinition')
      ->will($this->returnValue([
        'type' => '',
        'id' => 'rules_and',
        'label' => 'Condition set (AND).',
        'class' => 'Drupal\rules\Plugin\RulesExpression\RulesAnd',
        'provider' => 'rules',
      ]));

    return $rule;
  }

  /**
   * Creates an 'or' condition container with the basic plugin methods mocked.
   *
   * @param array $methods
   *   (optional) The methods to mock.
   *
   * @return \Drupal\rules\Engine\RulesConditionContainerInterface
   *   The mocked 'or' condition container.
   */
  public function getMockOr(array $methods = []) {
    $methods += ['getPluginId', 'getBasePluginId', 'getDerivativeId', 'getPluginDefinition'];

    $rule = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpression\RulesOr')
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();

    $rule->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('rules_or'));

    $rule->expects($this->any())
      ->method('getBasePluginId')
      ->will($this->returnValue('rules_or'));

    $rule->expects($this->any())
      ->method('getDerivativeId')
      ->will($this->returnValue(NULL));

    $rule->expects($this->any())
      ->method('getPluginDefinition')
      ->will($this->returnValue([
        'type' => '',
        'id' => 'rules_or',
        'label' => 'Condition set (OR).',
        'class' => 'Drupal\rules\Plugin\RulesExpression\RulesOr',
        'provider' => 'rules',
      ]));

    return $rule;
  }

}
