<?php

/**
 * @file
 * Contains \Drupal\rules\Core\RulesConditionBase.
 */

namespace Drupal\rules\Core;

use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\rules\Context\ContextHandlerTrait;

/**
 * Base class for rules conditions.
 */
abstract class RulesConditionBase extends ContextAwarePluginBase implements RulesConditionInterface {

  use ContextHandlerTrait;

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
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // @todo: Figure out whether this is useful to Rules somehow.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // @todo: Figure out whether this is useful to Rules somehow.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
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
