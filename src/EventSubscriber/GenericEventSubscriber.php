<?php

namespace Drupal\rules\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rules\Core\RulesConfigurableEventHandlerInterface;
use Drupal\rules\Core\RulesEventManager;
use Drupal\rules\Engine\ExecutionState;
use Drupal\rules\Engine\RulesComponentRepositoryInterface;
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
   * @var \Drupal\rules\Core\RulesEventManager
   */
  protected $eventManager;

  /**
   * The component repository.
   *
   * @var \Drupal\rules\Engine\RulesComponentRepositoryInterface
   */
  protected $componentRepository;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\rules\Core\RulesEventManager $event_manager
   *   The Rules event manager.
   * @param \Drupal\rules\Engine\RulesComponentRepositoryInterface $component_repository
   *   The component repository.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RulesEventManager $event_manager, RulesComponentRepositoryInterface $component_repository) {
    $this->entityTypeManager = $entity_type_manager;
    $this->eventManager = $event_manager;
    $this->componentRepository = $component_repository;
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
    // Get event metadata and the to be triggered events.
    $event_definition = $this->eventManager->getDefinition($event_name);
    $handler_class = $event_definition['class'];
    $triggered_events = [$event_name];
    if (is_subclass_of($handler_class, RulesConfigurableEventHandlerInterface::class)) {
      $qualified_event_suffixes = $handler_class::determineQualifiedEvents($event, $event_name, $event_definition);
      foreach ($qualified_event_suffixes as $qualified_event_suffix) {
        $triggered_events[] = "$event_name--$qualified_event_suffix";
      }
    }

    // Setup the execution state.
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

    $components = $this->componentRepository
      ->getMultiple($triggered_events, 'rules_event');

    foreach ($components as $component) {
      $component->getExpression()
        ->executeWithState($state);
    }
    $state->autoSave();
  }

}
