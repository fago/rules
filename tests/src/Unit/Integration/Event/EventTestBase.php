<?php

namespace Drupal\Tests\rules\Unit\Integration\Event;

use Drupal\rules\Core\RulesEventManager;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * Base class for testing Rules Event definitions.
 *
 * @group RulesEvent
 */
abstract class EventTestBase extends RulesEntityIntegrationTestBase {

  /**
   * The Rules event plugin manager.
   *
   * @var \Drupal\rules\Core\RulesEventManager
   */
  protected $eventManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->moduleHandler->getModuleDirectories()
      ->willReturn(['rules' => __DIR__ . '/../../../../../']);
    $this->eventManager = new RulesEventManager($this->moduleHandler->reveal());
  }

}
