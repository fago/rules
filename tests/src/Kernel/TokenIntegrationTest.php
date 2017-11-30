<?php

namespace Drupal\Tests\rules\Kernel;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;

/**
 * Test using the Rules API with the placeholder token replacement system.
 *
 * @group Rules
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class TokenIntegrationTest extends RulesDrupalTestBase {

  /**
   * Tests that date tokens are formatted correctly.
   */
  public function testSystemDateToken() {
    // Configure a simple rule with one action. and token replacements enabled.
    $action = $this->expressionManager->createInstance('rules_action',
      ContextConfig::create()
        ->setValue('message', "The date is {{ date | format_date('custom', 'Y-m') }}!")
        ->setValue('type', 'status')
        ->process('message', 'rules_tokens')
        ->setConfigKey('action_id', 'rules_system_message')
        ->toArray()
    );

    $rule = $this->expressionManager->createRule();
    $rule->addExpressionObject($action);
    RulesComponent::create($rule)
      ->addContextDefinition('date', ContextDefinition::create('timestamp'))
      ->setContextValue('date', REQUEST_TIME)
      ->execute();

    $messages = drupal_set_message();
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
    $date_formatter = $this->container->get('date.formatter');
    $date = $date_formatter->format(REQUEST_TIME, 'custom', 'Y-m');
    $this->assertEquals("The date is $date!", (string) $messages['status'][0]);
  }

}
