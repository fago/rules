<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\rules\Context\ContextDefinitionInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * Tests the Rules condition manager.
 *
 * @group RulesCondition
 */
class ConditionManagerTest extends RulesIntegrationTestBase {

  /**
   * @cover getDiscovery()
   */
  public function testContextDefinitionAnnotations() {
    $definitions = $this->conditionManager->getDefinitions();
    // Make sure all context definitions are using the class provided by Rules.
    foreach ($definitions as $definition) {
      if (!empty($definition['context'])) {
        foreach ($definition['context'] as $context_definition) {
          $this->assertInstanceOf(ContextDefinitionInterface::class, $context_definition);
        }
      }
    }
  }

}
