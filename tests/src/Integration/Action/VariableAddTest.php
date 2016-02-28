<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\VariableAddTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\VariableAdd
 * @group rules_actions
 */
class VariableAddTest extends RulesIntegrationTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

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
