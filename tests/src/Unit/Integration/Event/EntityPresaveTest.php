<?php

namespace Drupal\Tests\rules\Unit\Integration\Event;

/**
 * Checks that the entity presave events are defined.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\RulesEvent\EntityPresaveDeriver
 *
 * @group RulesEvent
 */
class EntityPresaveTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testEventMetadata() {
    $plugin_definition = $this->eventManager->getDefinition('rules_entity_presave:test');
    $this->assertSame('Before saving test', (string) $plugin_definition['label']);
    $context_definition = $plugin_definition['context']['test'];
    $this->assertSame('entity:test', $context_definition->getDataType());
    $this->assertSame('Test', $context_definition->getLabel());

    // Also check that there is a context for the original entity.
    $context_definition = $plugin_definition['context']['test_unchanged'];
    $this->assertSame('entity:test', $context_definition->getDataType());
    $this->assertSame('Unchanged Test', (string) $context_definition->getLabel());
  }

}
