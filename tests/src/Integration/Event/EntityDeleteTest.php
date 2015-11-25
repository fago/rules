<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Event\EntityDeleteTest.
 */

namespace Drupal\Tests\rules\Integration\Event;

/**
 * Checks that the entity delete events are defined.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\RulesEvent\EntityDeleteDeriver
 *
 * @group rules_events
 */
class EntityDeleteTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testEventMetadata() {
    $plugin_definition = $this->eventManager->getDefinition('rules_entity_delete:test');
    $this->assertSame('After deleting test', (string) $plugin_definition['label']);
    $context_definition = $plugin_definition['context']['test'];
    $this->assertSame('entity:test', $context_definition->getDataType());
    $this->assertSame('Test', $context_definition->getLabel());
  }

}
