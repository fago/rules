<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Engine\EventHandlerTest.
 */

namespace Drupal\Tests\rules\Integration\Engine;

use Drupal\rules\Context\ContextConfig;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * Tests events with qualified name.
 *
 * @group rules
 */
class EventHandlerTest extends RulesEntityIntegrationTestBase {

  /**
   * Tests EventHandlerEntityBundle configuration.
   */
  public function testEntityBundleHandlerConfiguration() {
    // - test the configuration-time case by reacting on an entity-bundle event
    //   and leveraging a bundle-specific field + run a successful integrity
    //   check on that.

    #1. mock somehow entity: 'node' with bundle 'page' and field 'body'
    #2. create somehow rule with action 'rules_entity_presave:node–page'
    #3. check somehow that node body is detected by the rule.

    // @todo: create node:page.

    $rule1 = $this->rulesExpressionManager->createRule();
    $rule1->addAction('rules_entity_presave:node', ContextConfig::create()
      ->map('entity', 'entity')
    );

    $rule2 = $this->rulesExpressionManager->createRule();
    $rule2->addAction('rules_entity_presave:node--page', ContextConfig::create()
      ->map('entity', 'entity')
    );

    // @todo: save node page and check if both rules are triggered.
  }

  /**
   * Tests EventHandlerEntityBundle execution.
   */
  public function testEntityBundleHandlerExecution() {
    // - Second we must cover execution time: Trigger that event and verify a
    //   reaction rule for the qualified event is correctly executed.

    #1. mock somehow entity: 'node' with bundle 'page' and field 'body'
    #2. create somehow rule with action 'rules_entity_presave:node–page'
    #3. node->save().
    #4. check somehow that the rule was triggered
  }

}
