<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\VariableAdd
 * @group RulesAction
 */
class VariableAddTest extends RulesIntegrationTestBase {

  /**
   * Test the action execution.
   *
   * @covers ::execute
   */
  public function testExecute() {
    $variable = 'test string';

    /** @var \Drupal\rules\Plugin\RulesAction\VariableAdd $action */
    $action = $this->actionManager->createInstance('rules_variable_add');
    $action->setContextValue('type', 'string');
    $action->setContextValue('value', $variable);
    $action->refineContextDefinitions([]);
    $action->execute();

    $result = $action->getProvidedContext('variable_added');
    $this->assertEquals($variable, $result->getContextValue());
    $this->assertEquals('string', $result->getContextDefinition()->getDataType());
  }

}
