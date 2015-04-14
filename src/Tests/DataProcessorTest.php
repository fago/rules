<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\DataProcessorTest.
 */

namespace Drupal\rules\Tests;

use Drupal\rules\Context\ContextConfig;

/**
 * Test the data processor plugins during Rules evaluation.
 *
 * @group rules
 */
class DataProcessorTest extends RulesDrupalTestBase {

  /**
   * Tests that the numeric offset plugin works.
   */
  public function testNumericOffset() {
    // Configure a simple rule with one action.
    $action = $this->expressionManager->createInstance('rules_action',
      // @todo Actually the data processor plugin only applies to numbers, so is
      // kind of an invalid configuration. Since the configuration is not
      // validated during execution this works for now.
      ContextConfig::create()
        ->map('message', 'message')
        ->map('type', 'type')
        ->process('message', 'rules_numeric_offset', [
          'offset' => 1,
        ])
        ->setConfigKey('action_id', 'rules_system_message')
        ->toArray()
    );

    $rule = $this->expressionManager->createRule([
      'context_definitions' => [
        'message' => [
          'type' => 'string',
        ],
        'type' => [
          'type' => 'string',
        ],
      ],
    ]);
    $rule->setContextValue('message', 1);
    $rule->setContextValue('type', 'status');
    $rule->addExpressionObject($action);
    $rule->execute();

    $messages = drupal_set_message();
    // The original value was 1 and the processor adds 1, so the result should
    // be 2.
    $this->assertEqual($messages['status'][0]['message'], '2');
  }

}
