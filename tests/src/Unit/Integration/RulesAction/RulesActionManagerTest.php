<?php

namespace Drupal\Tests\rules\Unit\Integration\RulesAction;

use Drupal\rules\Context\ContextDefinitionInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * Tests the Rules action manager.
 *
 * @group Rules
 */
class RulesActionManagerTest extends RulesIntegrationTestBase {

  /**
   * @cover getDiscovery()
   */
  public function testContextDefinitionAnnotations() {
    $definitions = $this->actionManager->getDefinitions();
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
