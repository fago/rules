<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\TokenIntegrationTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;

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

    $rule = $this->expressionManager->createRule([
      'context_definitions' => [
        'message' => ContextDefinition::create('string')->toArray(),
        'type' => ContextDefinition::create('string')->toArray(),
      ],
    ]);
    $rule->setContextValue('message', 'The date is [date:custom:Y-m]!');
    $rule->setContextValue('type', 'status');
    $rule->addExpressionObject($action);
    $rule->execute();

    $messages = drupal_set_message();
    $date = format_date(time(), 'custom', 'Y-m');
    $this->assertEqual($messages['status'][0]['message'], "The date is $date!");
  }

}
