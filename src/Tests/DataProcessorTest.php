<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\DataProcessorTest.
 */

namespace Drupal\rules\Tests;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Engine\RulesLog;
use Drupal\rules\Engine\RulesState;

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Clear the log from any stale entries that are bleeding over from previous
    // tests.
    $logger = RulesLog::logger();
    $logger->clear();
  }

  /**
   * Tests that the numeric offset plugin works.
   */
  public function testNumericOffset() {
    $rule = $this->createRulesRule();

    // Configure a simple rule with one action.
    $rule->addAction($this->rulesExpressionManager->createInstance('rules_action', [
      'action_id' => 'rules_drupal_message',
      'context_mapping' => ['text' => 0],
      // @todo Actually the data processor plugin only applies to numbers, so is
      // kind of an invalid configuration. Since the configuration is not
      // validated during execution this works for now.
      'processor_mapping' => [
        'text' => [
          'plugin' => 'rules_numeric_offset',
          'configuration' => [
            'offset' => 1,
          ]
        ],
      ],
    ]));
    $rule->execute();

    $messages = drupal_set_message();
    // The original value was 0 and the processor adds 1, so the result should
    // be 1.
    $this->assertEqual($messages['status'][0], '1');
  }

}
