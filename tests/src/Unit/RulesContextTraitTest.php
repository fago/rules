<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesContextTraitTest.
 */

namespace Drupal\Tests\rules\Unit;

/**
 * @coversDefaultClass \Drupal\rules\Context\RulesContextTrait
 * @group rules
 */
class RulesContextTraitTest extends RulesUnitTestBase {

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
   * @expectedException \Drupal\rules\Engine\RulesEvaluationException
   * @expectedExceptionMessage Required context test is missing for plugin testplugin.
   */
  public function testMissingContextMapping() {
    // Set 'getContextValue' as mocked method.
    $trait = $this->getMockForTrait('Drupal\rules\Context\RulesContextTrait', [], '', TRUE, TRUE, TRUE, ['getContextValue']);
    $trait->expects($this->once())
      ->method('getContextValue')
      ->with('test')
      ->will($this->returnValue(NULL));
    $context_definition = $this->getMock('Drupal\Core\Plugin\Context\ContextDefinitionInterface');

    // Make the context required in the definition.
    $context_definition->expects($this->once())
      ->method('isRequired')
      ->will($this->returnValue(TRUE));

    $plugin = $this->getMock('Drupal\Component\Plugin\ContextAwarePluginInterface');
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
