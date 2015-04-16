<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\ContextHandlerTraitTest.
 */

namespace Drupal\Tests\rules\Unit;

/**
 * @coversDefaultClass \Drupal\rules\Context\ContextHandlerTrait
 * @group rules
 */
class ContextHandlerTraitTest extends RulesUnitTestBase {

  /**
   * The mocked condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The condition object being tested.
   *
   * @var \Drupal\rules\Plugin\RulesExpression\RulesCondition
   */
  protected $condition;

  /**
   * Tests that a missing required context triggers an exception.
   *
   * @covers ::mapContext
   * @expectedException \Drupal\rules\Exception\RulesEvaluationException
   * @expectedExceptionMessage Required context test is missing for plugin testplugin.
   */
  public function testMissingContext() {
    // Set 'getContextValue' as mocked method.
    $trait = $this->getMockForTrait('Drupal\rules\Context\ContextHandlerTrait', [], '', TRUE, TRUE, TRUE, ['getContextValue']);
    $context_definition = $this->getMock('Drupal\Core\Plugin\Context\ContextDefinitionInterface');

    // Make the context required in the definition.
    $context_definition->expects($this->once())
      ->method('isRequired')
      ->will($this->returnValue(TRUE));

    $plugin = $this->getMock('Drupal\Core\Plugin\ContextAwarePluginInterface');
    $plugin->expects($this->once())
      ->method('getContextDefinitions')
      ->will($this->returnValue(['test' => $context_definition]));
    $plugin->expects($this->once())
      ->method('getPluginId')
      ->will($this->returnValue('testplugin'));

    $state = $this->getMock('Drupal\rules\Engine\RulesState');

    // Make the 'mapContext' method visible.
    $reflection = new \ReflectionClass($trait);
    $method = $reflection->getMethod('mapContext');
    $method->setAccessible(TRUE);
    $method->invokeArgs($trait, [$plugin, $state]);
  }

}
