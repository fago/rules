<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\TokenIntegrationTest.
 */

namespace Drupal\Tests\rules\Kernel;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;

/**
 * Test using the Rules API with the Token system.
 *
 * @group rules
 */
class TokenIntegrationTest extends RulesDrupalTestBase {

  /**
   * Tests that date tokens are formatted correctly.
   */
  public function testSystemDateToken() {
    // Configure a simple rule with one action. and token replacements enabled.
    $action = $this->expressionManager->createInstance('rules_action',
      ContextConfig::create()
        ->map('message', 'message')
        ->map('type', 'type')
        ->process('message', 'rules_tokens')
        ->setConfigKey('action_id', 'rules_system_message')
        ->toArray()
    );

    $rule = $this->expressionManager->createRule();
    $rule->addExpressionObject($action);
    RulesComponent::create($rule)
      ->addContextDefinition('message', ContextDefinition::create('string'))
      ->addContextDefinition('type', ContextDefinition::create('string'))
      ->setContextValue('message', 'The date is [date:custom:Y-m]!')
      ->setContextValue('type', 'status')
      ->execute();

    $messages = drupal_set_message();
    $date = format_date(time(), 'custom', 'Y-m');
    $this->assertEqual((string) $messages['status'][0], "The date is $date!");
  }

}
