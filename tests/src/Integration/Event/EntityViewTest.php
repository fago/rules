<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Event\EntityViewTest.
 */

namespace Drupal\Tests\rules\Integration\Event;

/**
 * Checks that the entity view events are defined.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\RulesEvent\EntityViewDeriver
 */
class EntityViewTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testEventMetadata() {
    $plugin_definition = $this->eventManager->getDefinition('rules_entity_view:entity_test_label');
    $this->assertSame('Entity Test label is viewed', $plugin_definition['label']);
    $context_definition = $plugin_definition['context']['entity_test_label'];
    $this->assertSame('entity:entity_test_label', $context_definition->getDataType());
    $this->assertSame('Entity Test label', $context_definition->getLabel());
  }

}
