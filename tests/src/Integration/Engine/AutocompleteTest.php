<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Engine\AutocompleteTest.
 */

namespace Drupal\Tests\rules\Integration\Engine;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * Test autocompletion of partial data selectors.
 *
 * @group rules
 */
class AutocompleteTest extends RulesEntityIntegrationTestBase {

  /**
   * Tests autocompletion works for a variable in the metadata state.
   */
  public function testAutocomplete() {
    $rule = $this->rulesExpressionManager->createRule();
    $action = $this->rulesExpressionManager->createAction('rules_action');
    $action->setConfiguration(ContextConfig::create()
      ->map('entity', 'entity')
      ->toArray()
    );
    $rule->addExpressionObject($action);

    $results = RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->autocomplete('e', $action);

    $this->assertSame(['entity'], $results);
  }

}
