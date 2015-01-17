<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\AddVariableTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\AddVariable
 * @group rules_action
 */
class AddVariableTest extends RulesIntegrationTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  public function testExecute() {
    $variable = $this->randomMachineName();

    /** @var \Drupal\rules\Plugin\Action\AddVariable $action */
    $action = $this->actionManager->createInstance('rules_add_variable');
    $action->setContextValue('value', $variable);
    $action->execute();

    $result = $action->getProvided('variable_added');
    $this->assertEquals($variable, $result->getContextValue());
  }
}
