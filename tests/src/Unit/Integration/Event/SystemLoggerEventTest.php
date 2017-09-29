<?php

namespace Drupal\Tests\rules\Unit\Integration\Event;

/**
 * Checks that the event "rules_system_logger_event" is correctly defined.
 *
 * @group RulesEvent
 */
class SystemLoggerEventTest extends EventTestBase {

  /**
   * Tests the event metadata.
   */
  public function testSystemLoggerEvent() {
    $event = $this->eventManager->createInstance('rules_system_logger_event');
    $logger_entry = $event->getContextDefinition('logger_entry');

    // @todo: create a TypedData logger-entry object: https://www.drupal.org/node/2625238
    $this->assertSame('any', $logger_entry->getDataType());
  }

}
