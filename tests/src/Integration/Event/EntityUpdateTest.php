<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Event\EntityUpdateTest.
 */

namespace Drupal\Tests\rules\Integration\Event;

/**
 * Checks that the entity update events are defined.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\RulesEvent\EntityUpdateDeriver
 *
 * @group rules_events
 */
class EntityUpdateTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testEventMetadata() {
    $plugin_definition = $this->eventManager->getDefinition('rules_entity_update:test');
    $this->assertSame('After updating test', (string) $plugin_definition['label']);
    $context_definition = $plugin_definition['context']['test'];
    $this->assertSame('entity:test', $context_definition->getDataType());
    $this->assertSame('Test', $context_definition->getLabel());
  }

}
