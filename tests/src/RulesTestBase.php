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

    $this->testAction = $this->getMockBuilder('Drupal\Core\Action\ActionInterface')
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Creates a rule with the basic plugin methods mocked.
   *
   * @param array $methods
   *   (optional) The methods to mock.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\RuleInterface
   *   The mocked rule.
   */
  public function getMockRule(array $methods = []) {
    $methods += ['getPluginId', 'getBasePluginId', 'getDerivativeId', 'getPluginDefinition'];

    $rule = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpression\Rule')
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();

    $rule->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('rules_rule'));

    $rule->expects($this->any())
      ->method('getBasePluginId')
      ->will($this->returnValue('rules_rule'));

    $rule->expects($this->any())
      ->method('getDerivativeId')
      ->will($this->returnValue(NULL));

    $rule->expects($this->any())
      ->method('getPluginDefinition')
      ->will($this->returnValue([
        'type' => '',
        'id' => 'rules_rule',
        'label' => 'A rule, executing actions when conditions are met.',
        'class' => 'Drupal\rules\Plugin\RulesExpression\Rule',
        'provider' => 'rules',
      ]));

    // Set the condition container that would otherwise get initialized in the
    // constructor.
    $rule->setConditions($this->getMockAnd());
    // Same for the actions container.
    $rule->setActions($this->getMockActionSet());

    return $rule;
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

    $and = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpression\RulesAnd')
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();

    $and->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('rules_and'));

    $and->expects($this->any())
      ->method('getBasePluginId')
      ->will($this->returnValue('rules_and'));

    $and->expects($this->any())
      ->method('getDerivativeId')
      ->will($this->returnValue(NULL));

    $and->expects($this->any())
      ->method('getPluginDefinition')
      ->will($this->returnValue([
        'type' => '',
        'id' => 'rules_and',
        'label' => 'Condition set (AND).',
        'class' => 'Drupal\rules\Plugin\RulesExpression\RulesAnd',
        'provider' => 'rules',
      ]));

    return $and;
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

    $or = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpression\RulesOr')
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();

    $or->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('rules_or'));

    $or->expects($this->any())
      ->method('getBasePluginId')
      ->will($this->returnValue('rules_or'));

    $or->expects($this->any())
      ->method('getDerivativeId')
      ->will($this->returnValue(NULL));

    $or->expects($this->any())
      ->method('getPluginDefinition')
      ->will($this->returnValue([
        'type' => '',
        'id' => 'rules_or',
        'label' => 'Condition set (OR).',
        'class' => 'Drupal\rules\Plugin\RulesExpression\RulesOr',
        'provider' => 'rules',
      ]));

    return $or;
  }

  /**
   * Creates an action set with the basic plugin methods mocked.
   *
   * @param array $methods
   *   (optional) The methods to mock.
   *
   * @return \Drupal\rules\Engine\RulesActionContainerInterface
   *   The mocked action container.
   */
  public function getMockActionSet(array $methods = []) {
    $methods += ['getPluginId', 'getBasePluginId', 'getDerivativeId', 'getPluginDefinition'];

    $action_set = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpression\ActionSet')
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();

    $action_set->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue('rules_action_set'));

    $action_set->expects($this->any())
      ->method('getBasePluginId')
      ->will($this->returnValue('rules_action_set'));

    $action_set->expects($this->any())
      ->method('getDerivativeId')
      ->will($this->returnValue(NULL));

    $action_set->expects($this->any())
      ->method('getPluginDefinition')
      ->will($this->returnValue([
        'type' => '',
        'id' => 'rules_action_set',
        'label' => 'Action set',
        'class' => 'Drupal\rules\Plugin\RulesExpression\ActionSet',
        'provider' => 'rules',
      ]));

    return $action_set;
  }

}
