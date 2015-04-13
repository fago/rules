<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\AutoSaveTest.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\rules\Plugin\RulesExpression\RulesAction;

/**
 * Test auto saving of variables after Rules execution.
 *
 * @group rules
 */
class AutoSaveTest extends RulesUnitTestBase {

  /**
   * Tests auto saving after an action execution.
   */
  public function testActionAutoSave() {
    $processor_manager = $this->getMockBuilder('Drupal\rules\Context\DataProcessorManager')
      ->disableOriginalConstructor()
      ->getMock();

    $action_manager = $this->getMockBuilder('Drupal\Core\Action\ActionManager')
      ->disableOriginalConstructor()
      ->getMock();

    $action_manager->expects($this->once())
      ->method('getDefinition')
      ->willReturn([
        'context' => [
          'entity' => $this->getMock('Drupal\Core\Plugin\Context\ContextDefinitionInterface')
        ],
      ]);

    $action_manager->expects($this->once())
      ->method('createInstance')
      ->willReturn($this->testActionExpression);

    $this->testActionExpression->expects($this->once())
      ->method('getContextDefinitions')
      ->willReturn(['entity' => $this->getMock('Drupal\Core\Plugin\Context\ContextDefinitionInterface')]);

    $this->testActionExpression->expects($this->once())
      ->method('getProvidedContextDefinitions')
      ->willReturn([]);

    $this->testActionExpression->expects($this->once())
      ->method('autoSaveContext')
      ->willReturn(['entity']);

    $action = new RulesAction([
      'action_id' => 'test',
    ], 'test', [], $action_manager, $processor_manager);

    $entity = $this->getMock('Drupal\Core\Entity\EntityInterface');
    $entity->expects($this->once())
      ->method('save');
    $entity_adapter = $this->getMock('\Drupal\Core\TypedData\ComplexDataInterface');
    $entity_adapter->expects($this->atLeastOnce())
      ->method('getValue')
      ->willReturn($entity);

    $context = $this->getMock('Drupal\Core\Plugin\Context\ContextInterface');
    $context->expects($this->once())
      ->method('getContextValue')
      ->willReturn($entity);
    $context->expects($this->once())
      ->method('getContextData')
      ->willReturn($entity_adapter);

    $action->setContext('entity', $context);

    $action->execute();
  }

}
