<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\DataProcessorTest.
 */

namespace Drupal\rules\Tests;

/**
 * Test the data processor plugins during Rules evaluation.
 */
class DataProcessorTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Data processor tests',
      'description' => 'Test the data processor plugins during Rules evaluation.',
      'group' => 'Rules',
    ];
  }

  /**
   * Tests that the numeric offset plugin works.
   */
  public function testNumericOffset() {
    $rule = $this->createRulesRule()
      ->addCondition($this->createRulesCondition('rules_test_true'));

    // Configure a simple rule with one action.
    $action = $this->rulesExpressionManager->createInstance('rules_action', [
      'action_id' => 'rules_drupal_message',
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
    ])->setContextValue('message', 1)
      ->setContextValue('type', 'status');
    $rule->addAction($action);
    $rule->execute();

    $messages = drupal_set_message();
    // The original value was 1 and the processor adds 1, so the result should
    // be 2.
    $this->assertEqual($messages['status'][0], '2');
  }

}
