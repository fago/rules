<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\RulesConditionBase.
 */

namespace Drupal\rules\Engine;

use \Drupal\Core\Executable\ExecutableManagerInterface;
use \Drupal\Core\StringTranslation\StringTranslationTrait;
use \Drupal\rules\Context\ContextAwarePluginBase;

/**
 * Base class for rules conditions.
 */
abstract class RulesConditionBase extends ContextAwarePluginBase implements RulesConditionInterface {

  use StringTranslationTrait;

  /**
   * The condition manager to proxy execute calls through.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $executableManager;

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * {@inheritdoc}
   */
  public function setExecutableManager(ExecutableManagerInterface $executableManager) {
    $this->executableManager = $executableManager;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isNegated() {
    return !empty($this->configuration['negate']);
  }

  /**
   * {@inheritdoc}
   */
  public function negate($negate = TRUE) {
    $this->configuration['negate'] = $negate;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    // @todo: Figure out whether this is useful to Rules somehow.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, array &$form_state) {
    // @todo: Figure out whether this is useful to Rules somehow.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
    // @todo: Figure out whether this is useful to Rules somehow.
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    return $this->executableManager->execute($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'id' => $this->getPluginId(),
    ] + $this->configuration;
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
  public function calculateDependencies() {
    return [];
  }

}
