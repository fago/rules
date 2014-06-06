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
   * @var \Drupal\rules\Engine\RulesActionInterface
   */
  protected $testAction;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->trueCondition = $this->getMock('Drupal\rules\Engine\RulesConditionInterface');

    $this->trueCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $this->trueCondition->expects($this->any())
      ->method('evaluate')
      ->will($this->returnValue(TRUE));

    $this->falseCondition = $this->getMock('Drupal\rules\Engine\RulesConditionInterface');

    $this->falseCondition->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));

    $this->falseCondition->expects($this->any())
      ->method('evaluate')
      ->will($this->returnValue(FALSE));

    $this->testAction = $this->getMock('Drupal\rules\Engine\RulesActionInterface');
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

    $this->expectsGetPluginId($rule, 'rules_rule')
      ->expectsGetDerivativeId($rule, NULL)
      ->expectsGetBasePluginId($rule, 'rules_rule')
      ->expectsGetPluginDefinition($rule, 'rules_rule', 'A rule, executing actions when conditions are met.');

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

    $this->expectsGetPluginId($and, 'rules_and')
      ->expectsGetDerivativeId($and, NULL)
      ->expectsGetBasePluginId($and, 'rules_and')
      ->expectsGetPluginDefinition($and, 'rules_and', 'Condition set (AND)');

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

    $this->expectsGetPluginId($or, 'rules_or')
      ->expectsGetDerivativeId($or, NULL)
      ->expectsGetBasePluginId($or, 'rules_or')
      ->expectsGetPluginDefinition($or, 'rules_or', 'Condition set (OR)');

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

    $actions = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpression\ActionSet')
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();

    $this->expectsGetPluginId($actions, 'rules_action_set')
      ->expectsGetDerivativeId($actions, NULL)
      ->expectsGetBasePluginId($actions, 'rules_action_set')
      ->expectsGetPluginDefinition($actions, 'rules_action_set', 'Rules Action');

    return $actions;
  }

  /**
   * Sets the mocked plugin to expect calls to 'getPluginId'.
   *
   * @param \PHPUnit_Framework_MockObject_MockObject $plugin
   *   The mocked plugin instance.
   * @param string $id
   *   (optional) The id of the plugin. Defaults to an empty string.
   *
   * @return $this
   *   The current object for chaining.
   */
  protected function expectsGetPluginId(\PHPUnit_Framework_MockObject_MockObject $plugin, $id = '') {
    $plugin->expects($this->any())
      ->method('getPluginId')
      ->will($this->returnValue($id));

    return $this;
  }

  /**
   * Sets the mocked plugin to expect calls to 'getBasePluginId'.
   *
   * @param \PHPUnit_Framework_MockObject_MockObject $plugin
   *   The mocked plugin instance.
   * @param string $id
   *   (optional) The base id of the plugin. Defaults to an empty string.
   *
   * @return $this
   *   The current object for chaining.
   */
  protected function expectsGetBasePluginId(\PHPUnit_Framework_MockObject_MockObject $plugin, $id = '') {
    $plugin->expects($this->any())
      ->method('getBasePluginId')
      ->will($this->returnValue($id));

    return $this;
  }

  /**
   * Sets the mocked plugin to expect calls to 'getDerivativeId'.
   *
   * @param \PHPUnit_Framework_MockObject_MockObject $plugin
   *   The mocked plugin instance.
   * @param string $id
   *   (optional) The derivative id of the plugin. Defaults to NULL.
   *
   * @return $this
   *   The current object for chaining.
   */
  protected function expectsGetDerivativeId(\PHPUnit_Framework_MockObject_MockObject $plugin, $id = NULL) {
    $plugin->expects($this->any())
      ->method('getDerivativeId')
      ->will($this->returnValue(NULL));

    return $this;
  }

  /**
   * Sets the mocked plugin to expect calls to 'getPluginDefinition'.
   *
   * @param \PHPUnit_Framework_MockObject_MockObject $plugin
   *   The mocked plugin instance.
   * @param string $id
   *   (optional) The id of the plugin. Defaults to an empty string.
   * @param string $label
   *   (optional) The label of the plugin. Defaults to NULL.
   * @param string $provider
   *   (optional) The name of the providing module. Defaults to 'rules'.
   * @param array $other
   *   (optional) Any other values to set as the plugin definition.
   *
   * @return $this
   *   The current object for chaining.
   */
  protected function expectsGetPluginDefinition(\PHPUnit_Framework_MockObject_MockObject $plugin, $id = '', $label = NULL, $provider = 'rules', array $other = []) {
    $defaults = [
      'type' => '',
      'id' => $id,
      'class' => get_class($plugin),
      'provider' => $provider,
    ];

    if (isset($label)) {
      $definition['label'] = $label;
    }

    $plugin->expects($this->any())
      ->method('getPluginDefinition')
      ->will($this->returnValue($other + $defaults));

    return $this;
  }

  /**
   * Creates a condition expression with the basic plugin methods mocked.
   *
   * @param array $methods
   *   (optional) The methods to mock.
   *
   * @return \Drupal\rules\Plugin\RulesExpression\RulesCondition
   *   The mocked condition expression.
   */
  public function getMockCondition(array $methods = []) {
    $methods += ['getPluginId', 'getBasePluginId', 'getDerivativeId', 'getPluginDefinition'];

    $condition = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpression\RulesCondition')
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();

    $this->expectsGetPluginId($condition, 'rules_condition')
      ->expectsGetDerivativeId($condition, NULL)
      ->expectsGetBasePluginId($condition, 'rules_condition')
      ->expectsGetPluginDefinition($condition, 'rules_condition', 'An executable condition');

    return $condition;
  }

}
