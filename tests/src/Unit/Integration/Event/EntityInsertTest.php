<?php

namespace Drupal\Tests\rules\Unit\Integration\Event;

/**
 * Checks that the entity insert events are defined.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\RulesEvent\EntityInsertDeriver
 *
 * @group RulesEvent
 */
class EntityInsertTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testEventMetadata() {
    $plugin_definition = $this->eventManager->getDefinition('rules_entity_insert:test');
    $this->assertSame('After saving new test', (string) $plugin_definition['label']);
    $context_definition = $plugin_definition['context']['test'];
    $this->assertSame('entity:test', $context_definition->getDataType());
    $this->assertSame('Test', $context_definition->getLabel());
  }

}
