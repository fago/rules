<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesUnitTestBase.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\Plugin\DataType\Any;
use Drupal\Tests\UnitTestCase;

/**
 * Helper class with mock objects.
 */
abstract class RulesUnitTestBase extends UnitTestCase {

  /**
   * A mocked condition that always evaluates to TRUE.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionInterface
   */
  protected $trueConditionExpression;

  /**
   * A mocked condition that always evaluates to FALSE.
   *
   * @var \Drupal\rules\Engine\ConditionExpressionInterface
   */
  protected $falseConditionExpression;

  /**
   * A mocked dummy action object.
   *
   * @var \Drupal\rules\Engine\ActionExpressionInterface
   */
  protected $testActionExpression;

  /**
   * The mocked expression manager object.
   *
   * @var \Drupal\rules\Engine\ExpressionPluginManager
   */
  protected $expressionManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->trueConditionExpression = $this->getMock('Drupal\rules\Engine\ConditionExpressionInterface');

    $this->trueConditionExpression->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $this->trueConditionExpression->expects($this->any())
      ->method('executeWithState')
      ->will($this->returnValue(TRUE));

    $this->trueConditionExpression->expects($this->any())
      ->method('evaluate')
      ->will($this->returnValue(TRUE));

    $this->falseConditionExpression = $this->getMock('Drupal\rules\Engine\ConditionExpressionInterface');

    $this->falseConditionExpression->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));

    $this->falseConditionExpression->expects($this->any())
      ->method('executeWithState')
      ->will($this->returnValue(FALSE));

    $this->falseConditionExpression->expects($this->any())
      ->method('evaluate')
      ->will($this->returnValue(FALSE));

    $this->testActionExpression = $this->getMock('Drupal\rules\Engine\ActionExpressionInterface');

    $this->expressionManager = $this->getMockBuilder('Drupal\rules\Engine\ExpressionPluginManager')
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * Creates a typed data mock with a given value.
   *
   * @param mixed $value
   *   The value to set in the mocked typed data object.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\TypedData\TypedDataInterface
   *   The mocked typed data object with the given value set.
   */
  protected function getMockTypedData($value) {
    $typed_data = $this->getMock('Drupal\Core\TypedData\TypedDataInterface');

    $typed_data->expects($this->any())
      ->method('getValue')
      ->will($this->returnValue($value));

    return $typed_data;
  }

  /**
   * Creates a typed data manager with the basic data type methods mocked.
   *
   * @param array $methods
   *   (optional) The methods to mock.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\TypedData\TypedDataManager
   *   The mocked typed data manager
   *
   * @see \Drupal\Core\TypedData\TypedDataManager
   */
  protected function getMockTypedDataManager(array $methods = []) {
    $methods += ['createDataDefinition', 'createListDataDefinition', 'createInstance'];

    $typed_data_manager = $this->getMockBuilder('Drupal\Core\TypedData\TypedDataManager')
      ->setMethods($methods)
      ->disableOriginalConstructor()
      ->getMock();

    // These can be overridden in the test implementation to return more
    // specific data definitions.
    $typed_data_manager->expects($this->any())
      ->method('createDataDefinition')
      ->with($this->anything())
      ->will($this->returnCallback(function ($data) {
        return DataDefinition::create($data);
      }));

    $typed_data_manager->expects($this->any())
      ->method('createListDataDefinition')
      ->with($this->anything())
      ->will($this->returnCallback(function ($data) {
        return DataDefinition::create($data);
      }));

    $typed_data_manager->expects($this->any())
      ->method('createInstance')
      ->with($this->anything())
      ->will($this->returnCallback(function ($definition, $configuration) {
        // We don't care for validation in our condition plugin tests. Therefore
        // we wrap all the data in a simple 'any' data type. That way we can use
        // all the data setters and getters without running into any problems or
        // needless complexity and mocking.
        // @see \Drupal\Core\TypedData\TypedDataManager::createInstance.
        return new Any($definition, $configuration['name'], $configuration['parent']);
      }));

    return $typed_data_manager;
  }

  /**
   * Creates a string translation with the basic translation methods mocked.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   *   The mocked string translation.
   *
   * @see \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected function getMockStringTranslation() {
    $string_translation = $this->getMock('Drupal\Core\StringTranslation\TranslationInterface');
    $string_translation->expects($this->any())
      ->method('translate')
      ->will($this->returnCallback(function ($string) {
        return $string;
      }));

    $string_translation->expects($this->any())
      ->method('formatPlural')
      ->will($this->returnCallback(function($count, $one, $multiple) {
        return $count == 1 ? $one : str_replace('@count', $count, $multiple);
      }));

    return $string_translation;
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
  protected function getMockRule(array $methods = []) {
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
   * @return \Drupal\rules\Engine\ConditionExpressionContainerInterface
   *   The mocked 'and' condition container.
   */
  protected function getMockAnd(array $methods = []) {
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
   * @return \Drupal\rules\Engine\ConditionExpressionContainerInterface
   *   The mocked 'or' condition container.
   */
  protected function getMockOr(array $methods = []) {
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
   * @return \Drupal\rules\Engine\ActionExpressionContainerInterface
   *   The mocked action container.
   */
  protected function getMockActionSet(array $methods = []) {
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

}
