<?php

/**
 * @file
 * Contains \Drupal\rules\EventHandler\ConfigurableEventHandlerEntityBundle.
 */

namespace Drupal\rules\EventHandler;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Exposes the bundle of an entity as event setting.
 */
class ConfigurableEventHandlerEntityBundle extends ConfigurableEventHandlerBase {

  /**
   * The bundles information for the entity.
   *
   * @var array
   */
  protected $bundlesInfo;

  /**
   * The entity info plugin definition.
   *
   * @var mixed
   */
  protected $entityInfo;

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityType = $this->getEventNameSuffix();
    $this->entityInfo = \Drupal::entityTypeManager()->getDefinition($this->entityType);
    $this->bundlesInfo = \Drupal::entityManager()->getBundleInfo($this->entityType);
    if (!$this->bundlesInfo) {
      throw new \InvalidArgumentException('Unsupported event name passed.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function determineQualifiedEvents(Event $event, $event_name, array &$event_definition) {
    $events_suffixes = [];
    if ($event instanceof \Drupal\rules\Event\EntityEvent) {
      $events_suffixes[] = $event->getSubject()->bundle();
    }
    return $events_suffixes;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $bundle = $this->configuration['bundle'];
    $bundle_label = isset($this->bundlesInfo[$bundle]['label']) ? $this->bundlesInfo[$bundle]['label'] : $bundle;
    $suffix = isset($bundle) ? ' ' . t('of @bundle-key %name', array('@bundle-key' => $this->entityInfo->getBundleLabel(), '%name' => $bundle_label)) : '';
    return $this->pluginDefinition['label']->render() . $suffix;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['bundle'] = array(
      '#type' => 'select',
      '#title' => t('Restrict by @bundle', array('@bundle' => $this->entityInfo->getBundleLabel())),
      '#description' => t('If you need to filter for multiple values, either add multiple events or use the "Entity is of bundle" condition instead.'),
      '#default_value' => $this->configuration['bundle'],
      '#empty_value' => '',
    );
    foreach ($this->bundlesInfo as $name => $bundle_info) {
      $form['bundle']['#options'][$name] = $bundle_info['label'];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function extractConfigurationFormValues(array &$form, FormStateInterface $form_state) {
    $this->configuration['bundle'] = $form_state->getValue('bundle');
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
    $parts = explode(':', $this->pluginId);
    return $parts[1];
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
