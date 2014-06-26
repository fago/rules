<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\ConditionTestBase.
 */

namespace Drupal\rules\Tests\Condition;

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
   * @param bool $constructor
   *   (optional) Whether to use the original constructor. Defaults to FALSE.
   * @param array $arguments
   *   (optional) The arguments to pass to the constructor. Defaults to an
   *   empty array.
   *
   * @throws \LogicException
   *   If the given class does not implement RulesConditionInterface.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Drupal\rules\Engine\RulesConditionInterface
   *   The mocked condition plugin.
   *
   * @see \Drupal\rules\Engine\RulesConditionInterface
   */
  public function getMockCondition($class, $id = '', array $methods = [], $constructor = FALSE, $arguments = []) {
    $reflection = new \ReflectionClass($class);
    if (!$reflection->implementsInterface('\Drupal\rules\Engine\RulesConditionInterface')) {
      throw new \LogicException(sprintf('%s does not implement the RulesConditionInterface.', array('%class' => $class)));
    }

    $methods += ['getPluginId', 'getBasePluginId', 'getDerivativeId', 'getPluginDefinition'];

    $condition = $this->getMockBuilder($class)
      ->setMethods($methods);

    if (empty($constructor)) {
      // Disable the constructor unless specified otherwise.
      $condition = $condition->disableOriginalConstructor()
        ->getMock();

      // Set the default typed data manager mock. This can be overridden in
      // the test implementation with a more specific typed data manager mock.
      // This is only necessary if we are not using the original constructor.
      $condition->setTypedDataManager($this->getMockTypedDataManager());
    }
    else {
      // Pass the given arguments to the constructor.
      $condition = $condition->setConstructorArgs($arguments)
        ->getMock();
    }

    $this->expectsGetPluginId($condition, $id)
      ->expectsGetBasePluginId($condition, $id)
      ->expectsGetDerivativeId($condition, NULL)
      ->expectsGetPluginDefinition($condition, $id);

    // Set the default string translation mock. This can be overridden in the
    // test implementation with a more specific string translation mock.
    $condition->setStringTranslation($this->getMockStringTranslation());

    return $condition;
  }

  /**
   * Creates a string translation with the basic translation methods mocked.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\StringTranslation\TranslationInterface
   *   The mocked string translation.
   *
   * @see \Drupal\Core\StringTranslation\TranslationInterface
   */
  public function getMockStringTranslation() {
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

}
