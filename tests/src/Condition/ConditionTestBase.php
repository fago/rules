<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\ConditionTestBase.
 *
 * Condition plugins commonly use the global function t() in their static
 * contextDefinitions() method. Hence, in order to make the unit tests work, we
 * have to provide a fallback for t() in the global namespace.
 */

namespace Drupal\rules\Tests\Condition {

  use Drupal\Core\TypedData\DataDefinition;
  use Drupal\Core\TypedData\Plugin\DataType\Any;
  use Drupal\rules\Tests\RulesTestBase;

  /**
   * Helper class with mock objects.
   */
  abstract class ConditionTestBase extends RulesTestBase {

    /**
     * Creates a rule with the basic plugin methods mocked.
     *
     * @param string $class
     *   The condition plugin class.
     * @param string $id
     *   (optional) The id of the condition plugin.
     * @param array $methods
     *   (optional) The methods to mock.
     *
     * @throws \LogicException
     *   If the given class does not implement RulesConditionInterface.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     *   The mocked condition plugin.
     *
     * @see \Drupal\rules\Engine\RulesConditionInterface
     */
    public function getMockCondition($class, $id = '', array $methods = []) {
      $reflection = new \ReflectionClass($class);
      if (!$reflection->implementsInterface('\Drupal\rules\Engine\RulesConditionInterface')) {
        throw new \LogicException(sprintf('%s does not implement the RulesConditionInterface.', array('%class' => $class)));
      }

      $methods += ['getPluginId', 'getBasePluginId', 'getDerivativeId', 'getPluginDefinition'];

      $condition = $this->getMockBuilder($class)
        ->setMethods($methods)
        ->disableOriginalConstructor()
        ->getMock();

      $this->expectsGetPluginId($condition, $id)
        ->expectsGetBasePluginId($condition, $id)
        ->expectsGetDerivativeId($condition, NULL)
        ->expectsGetPluginDefinition($condition, $id);

      // Set the default typed data manager mock. This can be overridden in the
      // test implementation with a more specific typed data manager mock.
      $condition->setTypedDataManager($this->getMockTypedDataManager());

      return $condition;
    }

    /**
     * Creates a typed data manager with the basic data type methods mocked.
     *
     * @param array $methods
     *   (optional) The methods to mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     *   The mocked typed data manager
     *
     * @see \Drupal\Core\TypedData\TypedDataManager
     */
    public function getMockTypedDataManager(array $methods = []) {
      $methods += ['createDataDefinition', 'createInstance'];

      $typed_data_manager = $this->getMockBuilder('Drupal\Core\TypedData\TypedDataManager')
        ->setMethods($methods)
        ->disableOriginalConstructor()
        ->getMock();

      // This can be overridden in the test implementation to return a more
      // specific data definition.
      $typed_data_manager->expects($this->any())
        ->method('createDataDefinition')
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
  }
}

namespace {
  if (!function_exists('t')) {
    function t($string, array $args = array(), array $options = array()) {
      return $string;
    }
  }
}
