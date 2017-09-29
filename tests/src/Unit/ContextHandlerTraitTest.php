<?php

namespace Drupal\Tests\rules\Unit;

use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinitionInterface;
use Drupal\rules\Context\ContextHandlerTrait;
use Drupal\rules\Engine\ExecutionStateInterface;

/**
 * @coversDefaultClass \Drupal\rules\Context\ContextHandlerTrait
 * @group Rules
 */
class ContextHandlerTraitTest extends RulesUnitTestBase {

  /**
   * Tests that a missing required context triggers an exception.
   *
   * @covers ::prepareContext
   */
  public function testMissingContext() {
    // Set the expected exception class and message.
    $this->setExpectedException('\Drupal\rules\Exception\EvaluationException', 'Required context test is missing for plugin testplugin');

    // Set 'getContextValue' as mocked method.
    $trait = $this->getMockForTrait(ContextHandlerTrait::class, [], '', TRUE, TRUE, TRUE, ['getContextValue']);
    $context_definition = $this->prophesize(ContextDefinitionInterface::class);

    // Let the trait work with an empty configuration.
    $trait->configuration = ContextConfig::create()->toArray();

    // Make the context required in the definition.
    $context_definition->isRequired()->willReturn(TRUE)->shouldBeCalledTimes(1);

    $plugin = $this->prophesize(ContextAwarePluginInterface::class);
    $plugin->getContextDefinitions()
      ->willReturn(['test' => $context_definition->reveal()])
      ->shouldBeCalled(1);
    $plugin->getContextValue('test')
      ->willReturn(NULL)
      ->shouldBeCalled(1);
    $plugin->getPluginId()->willReturn('testplugin')->shouldBeCalledTimes(1);

    $state = $this->prophesize(ExecutionStateInterface::class);

    // Make the 'mapContext' method visible.
    $reflection = new \ReflectionClass($trait);
    $method = $reflection->getMethod('prepareContext');
    $method->setAccessible(TRUE);
    $method->invokeArgs($trait, [$plugin->reveal(), $state->reveal()]);
  }

}
