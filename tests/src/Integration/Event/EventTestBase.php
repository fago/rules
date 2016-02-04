<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Event\EventTestBase.
 */

namespace Drupal\Tests\rules\Integration\Event;

use Drupal\rules\Core\RulesEventManager;
use Drupal\Tests\rules\Integration\RulesEntityIntegrationTestBase;

/**
 * Base class for testing Rules Event definitions.
 *
 * @group rules_events
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
      ->willReturn(['rules' => __DIR__ . '/../../../..']);
    $this->eventManager = new RulesEventManager($this->moduleHandler->reveal());
  }

}
