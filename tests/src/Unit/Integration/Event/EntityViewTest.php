<?php

namespace Drupal\Tests\rules\Unit\Integration\Event;

/**
 * Checks that the entity view events are defined.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\RulesEvent\EntityViewDeriver
 *
 * @group RulesEvent
 */
class EntityViewTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testEventMetadata() {
    $plugin_definition = $this->eventManager->getDefinition('rules_entity_view:test');
    $this->assertSame('Test is viewed', (string) $plugin_definition['label']);
    $context_definition = $plugin_definition['context']['test'];
    $this->assertSame('entity:test', $context_definition->getDataType());
    $this->assertSame('Test', $context_definition->getLabel());
  }

}
