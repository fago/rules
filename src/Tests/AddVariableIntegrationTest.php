<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\AddVariableIntegrationTest.
 */

namespace Drupal\rules\Tests;

/**
 * Test the data processor plugins during Rules evaluation.
 *
 * @group rules
 */
class AddVariableIntegrationTest extends RulesDrupalTestBase {

  public function testExecute() {
    $variable = $this->randomMachineName();

    /** @var \Drupal\rules\Plugin\Action\AddVariable $action */
    $action = $this->actionManager->createInstance('rules_add_variable');
    $action->setContextValue('value', $variable);
    $action->execute();

    $result = $action->getProvided('variable_added');
    $this->assertEqual($variable, $result);
  }

}

