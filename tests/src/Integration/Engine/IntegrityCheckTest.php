<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Engine\IntegrityCheckTest.
 */

namespace Drupal\Tests\rules\Integration\Engine;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * Test the integrity check functionality during configuration time.
 *
 * @group rules
 */
class IntegrityCheckTest extends RulesEntityIntegrationTestBase {

  /**
   * Tests that the integrity check can be invoked.
   */
  public function testIntegrityCheck() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'entity')
    );

    $violation_list = RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->checkIntegrity();
    $this->assertEquals(iterator_count($violation_list), 0);
  }

  /**
   * Tests that a wrongly configured variable name triggers a violation.
   */
  public function testUnknownVariable() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'unknown_variable')
    );

    $violation_list = RulesComponent::create($rule)
      ->checkIntegrity();
    $this->assertEquals(iterator_count($violation_list), 1);
    $violation = $violation_list[0];
    $this->assertEquals(
      'Data selector <em class="placeholder">unknown_variable</em> for context <em class="placeholder">Entity</em> is invalid. Unable to get variable unknown_variable, it is not defined.',
      (string) $violation->getMessage()
    );
  }

  /**
   * Tests that the integrity check with UUID works.
   */
  public function testCheckUuid() {
    $rule = $this->rulesExpressionManager->createRule();
    // Just use a rule with 2 dummy actions.
    $rule->addAction('rules_entity_save', ContextConfig::create()
          ->map('entity', 'unknown_variable_1'))
        ->addAction('rules_entity_save', ContextConfig::create()
          ->map('entity', 'unknown_variable_2'));

    $all_violations = RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->checkIntegrity();

    $this->assertEquals(2, iterator_count($all_violations));

    // Get the UUID of the second action.
    $iterator = $rule->getIterator();
    $iterator->next();
    $uuid = $iterator->key();

    $uuid_violations = $all_violations->getFor($uuid);
    $this->assertEquals(1, count($uuid_violations));
    $violation = $uuid_violations[0];
    $this->assertEquals(
      'Data selector <em class="placeholder">unknown_variable_2</em> for context <em class="placeholder">Entity</em> is invalid. Unable to get variable unknown_variable_2, it is not defined.',
      (string) $violation->getMessage()
    );
  }

}
