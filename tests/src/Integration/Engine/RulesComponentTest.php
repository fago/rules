<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Engine\RulesComponentTest.
 */

namespace Drupal\Tests\rules\Integration\Engine;

use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * Tests the Rules component class.
 *
 * @group rules
 *
 * @cover RulesComponent
 */
class RulesComponentTest extends RulesIntegrationTestBase {

  /**
   * Tests executing a rule providing context based upon given context.
   */
  public function testRuleExecutionWithContext() {
    $rule = $this->rulesExpressionManager->createRule();

    $rule->addAction('rules_test_string',
      ContextConfig::create()->map('text', 'text')
    );

    $result = RulesComponent::create($rule)
      ->addContextDefinition('text', ContextDefinition::create('string'))
      ->setContextValue('text', 'foo')
      ->execute();

    // @todo: Provide variables back.
    // Ensure the provided context is returned.
    // $this->assertTrue(isset($result['concatenated']) && $result['concatenated'] == 'foo.foo');
  }

}
