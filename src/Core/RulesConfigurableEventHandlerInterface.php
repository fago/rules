<?php

namespace Drupal\rules\Core;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Interface for handling configurable rules events.
 *
 * Configurable events have a custom event suffix, which gets appended to the
 * base event name (= the plugin id), forming the fully-qualified event name,
 * which is used for triggering reaction rules.
 *
 * For example, the fully-qualified event name of an event for viewing an
 * article node would be "rules_entity_view:node--article", whereas
 * "rules_entity_view:node" is the base event name and "article" the event
 * suffix as returned from ::getEventNameSuffix().
 *
 * The event name suffix must be generated from the event data at run-time,
 * while the configured plugin has to determine it based upon the event
 * configuration.
 *
 * @see \Drupal\rules\Core\RulesDefaultEventHandler
 */
interface RulesConfigurableEventHandlerInterface extends RulesEventHandlerInterface, ConfigurablePluginInterface {

  /**
   * Determines the qualified event names for the dispatched event.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The event data of the event being dispatched.
   * @param string $event_name
   *   The event base name.
   * @param array $event_definition
   *   The event definition. If necessary for the event, the contained context
   *   definitions may be refined as suiting for the event data.
   *
   * @return string[]
   *   The array of qualified event name suffixes to add; e.g, 'article' if
   *   the fully-qualified event "rules_entity_view:node--article" should be
   *   triggered in addition to base event "rules_entity_view:node".
   */
  public static function determineQualifiedEvents(Event $event, $event_name, array &$event_definition);

  /**
   * Provides a human readable summary of the event's configuration.
   *
   * @return string|\Drupal\Component\Render\MarkupInterface
   *   The human readable summary.
   */
  public function summary();

  /**
   * Builds the event configuration form.
   *
   * @param array $form
   *   An associative array containing the initial structure of the plugin form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   *
   * @return array
   *   The form structure.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state);

  /**
   * Extract the form values and update the event configuration.
   *
   * @param array $form
   *   An associative array containing the structure of the plugin form as built
   *   by static::buildConfigurationForm().
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the complete form.
   */
  public function extractConfigurationFormValues(array &$form, FormStateInterface $form_state);

  /**
   * Validates that this event is configured correctly.
   *
   * @return \Drupal\rules\Engine\IntegrityViolationList
   *   A list object containing \Drupal\rules\Engine\IntegrityViolation objects.
   */
  public function validate();

  /**
   * Provides the event name suffix based upon the plugin configuration.
   *
   * If the event is configured and a suffix is provided, the event name Rules
   * uses for the configured event is {EVENT_NAME}--{SUFFIX}.
   *
   * @return string|false
   *   The suffix string, for FALSE if no suffix should be appended.
   */
  public function getEventNameSuffix();

  /**
   * Refines provided context definitions based upon plugin configuration.
   */
  public function refineContextDefinitions();

}
