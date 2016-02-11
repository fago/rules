<?php

/**
 * @file
 * Contains \Drupal\rules\EventHandler\ConfigurableEventHandlerBase.
 */

namespace Drupal\rules\EventHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Core\RulesConfigurableEventHandlerInterface;
use Drupal\rules\Core\RulesDefaultEventHandler;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base class for event handler.
 */
abstract class ConfigurableEventHandlerBase extends RulesDefaultEventHandler implements RulesConfigurableEventHandlerInterface {

  /**
   * The event configuration.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * {@inheritdoc}
   */
  public static function determineQualifiedEvents(Event $event, $event_name, array &$event_definition) {
    // Nothing to do by default.
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
  public function extractConfigurationFormValues(array &$form, FormStateInterface $form_state) {
    foreach ($this->defaultConfiguration() as $key => $configuration) {
      $this->configuration[$key] = $form_state->hasValue($key) ? $form_state->getValue($key) : $configuration;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
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
