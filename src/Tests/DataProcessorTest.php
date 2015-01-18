<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\DataProcessorTest.
 */

namespace Drupal\rules\Tests;

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
    $action = $this->rulesExpressionManager->createInstance('rules_action', [
      'action_id' => 'rules_system_message',
      // @todo Actually the data processor plugin only applies to numbers, so is
      // kind of an invalid configuration. Since the configuration is not
      // validated during execution this works for now.
      'processor_mapping' => [
        'message' => [
          'plugin' => 'rules_numeric_offset',
          'configuration' => [
            'offset' => 1,
          ],
        ],
      ],
    ]);

    $action->setContextValue('message', 1)
      ->setContextValue('type', 'status');

    $this->createRulesRule()
      ->addCondition($this->createRulesCondition('rules_test_true'))
      ->addAction($action)
      ->execute();

    $messages = drupal_set_message();
    // The original value was 1 and the processor adds 1, so the result should
    // be 2.
    $this->assertEqual($messages['status'][0]['message'], '2');
  }

}
