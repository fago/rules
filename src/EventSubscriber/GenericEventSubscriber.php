<?php

/**
 * @file
 * Contains \Drupal\rules\EventSubscriber\GenericEventSubscriber.
 */

namespace Drupal\rules\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rules\Engine\RulesEventManager;
use Drupal\rules\Engine\ExecutionState;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscribes to Symfony events and maps them to Rules events.
 */
class GenericEventSubscriber implements EventSubscriberInterface {

  /**
   * The entity manager used for loading reaction rule config entities.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Rules event manager.
   *
   * @var \Drupal\rules\Engine\RulesEventManager
   */
  protected $eventManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\rules\Engine\RulesEventManager $event_manager
   *   The Rules event manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RulesEventManager $event_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->eventManager = $event_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Register this listener for every event that is used by a reaction rule.
    $events = [];
    $callback = ['onRulesEvent', 100];

    // If there is no state service there is nothing we can do here. This static
    // method could be called early when the container is built, so the state
    // service might no always be available.
    if (!\Drupal::hasService('state')) {
      return [];
    }

    // Since we cannot access the reaction rule config storage here we have to
    // use the state system to provide registered Rules events. The Reaction
    // Rule storage is responsible for keeping the registered events up to date
    // in the state system.
    // @see \Drupal\rules\Entity\ReactionRuleStorage
    $state = \Drupal::state();
    $registered_event_names = $state->get('rules.registered_events');
    if (!empty($registered_event_names)) {
      foreach ($registered_event_names as $event_name) {
        $events[$event_name][] = $callback;
      }
    }
    return $events;
  }

  /**
   * Reacts on the given event and invokes configured reaction rules.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The event object containing context for the event.
   * @param string $event_name
   *   The event name.
   */
  public function onRulesEvent(Event $event, $event_name) {
    // Load reaction rule config entities by $event_name.
    $storage = $this->entityTypeManager->getStorage('rules_reaction_rule');
    // @todo Only load active reaction rules here.
    $configs = $storage->loadByProperties(['event' => $event_name]);

    // Set up an execution state with the event context.
    $event_definition = $this->eventManager->getDefinition($event_name);
    $state = ExecutionState::create();
    foreach ($event_definition['context'] as $context_name => $context_definition) {
      // If this is a GenericEvent get the context for the rule from the event
      // arguments.
      if ($event instanceof GenericEvent) {
        $value = $event->getArgument($context_name);
      }
      // Else there must be a getter method or public property.
      // @todo: Add support for the getter method.
      else {
        $value = $event->$context_name;
      }
      $state->setVariable(
        $context_name,
        $context_definition,
        $value
      );
    }

    // Loop over all rules and execute them.
    foreach ($configs as $config) {
      /** @var \Drupal\rules\Entity\ReactionRuleConfig $config */
      $config->getExpression()
        ->executeWithState($state);
    }
    $state->autoSave();
  }

}
