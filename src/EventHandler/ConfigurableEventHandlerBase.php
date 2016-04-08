<?php

namespace Drupal\rules\EventHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Core\RulesConfigurableEventHandlerInterface;
use Drupal\rules\Core\RulesDefaultEventHandler;

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

}
