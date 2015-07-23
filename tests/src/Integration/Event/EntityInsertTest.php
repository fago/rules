<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Event\EntityInsertTest.
 */

namespace Drupal\Tests\rules\Integration\Event;

/**
 * Checks that the entity insert events are defined.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\RulesEvent\EntityInsertDeriver
 */
class EntityInsertTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testEventMetadata() {
    $plugin_definition = $this->eventManager->getDefinition('rules_entity_insert:entity_test_label');
    $this->assertSame('After saving new entity test label', $plugin_definition['label']);
    $context_definition = $plugin_definition['context']['entity_test_label'];
    $this->assertSame('entity:entity_test_label', $context_definition->getDataType());
    $this->assertSame('Entity Test label', $context_definition->getLabel());
  }

}
