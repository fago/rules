<?php

namespace Drupal\rules\EventHandler;

use Drupal\rules\Event\EntityEvent;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Exposes the bundle of an entity as event setting.
 */
class ConfigurableEventHandlerEntityBundle extends ConfigurableEventHandlerBase {

  /**
   * {@inheritdoc}
   */
  public static function determineQualifiedEvents(Event $event, $event_name, array &$event_definition) {
    $events_suffixes = [];
    if ($event instanceof EntityEvent) {
      $events_suffixes[] = $event->getSubject()->bundle();
    }
    return $events_suffixes;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    // Nothing to do by default.
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Nothing to do by default.
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    // Nothing to check by default.
  }

  /**
   * {@inheritdoc}
   */
  public function getEventNameSuffix() {
    // Nothing to do by default.
  }

  /**
   * {@inheritdoc}
   */
  public function refineContextDefinitions() {
    // Nothing to refine by default.
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    // Nothing to calculate by default.
  }

}
