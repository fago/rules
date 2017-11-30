<?php

namespace Drupal\Tests\rules\Kernel;

use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Engine\RulesComponent;

/**
 * Test the data processor plugins during Rules evaluation.
 *
 * @group Rules
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
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

    $component = RulesComponent::create($this->expressionManager->createRule())
      ->addContextDefinition('message', ContextDefinition::create('string'))
      ->addContextDefinition('type', ContextDefinition::create('string'))
      ->setContextValue('message', 1)
      ->setContextValue('type', 'status');

    $component->getExpression()
      ->addExpressionObject($action);

    $component->execute();

    $messages = drupal_set_message();
    // The original value was 1 and the processor adds 1, so the result should
    // be 2.
    $this->assertEquals((string) $messages['status'][0], '2');
  }

}
