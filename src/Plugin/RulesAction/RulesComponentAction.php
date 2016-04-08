<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a generic 'Execute Rules component' action.
 *
 * @RulesAction(
 *   id = "rules_component",
 *   deriver = "Drupal\rules\Plugin\RulesAction\RulesComponentActionDeriver"
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class RulesComponentAction extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The storage of rules components.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The ID of the rules component config entity.
   *
   * @var string
   */
  protected $componentId;

  /**
   * List of context names that should be saved later.
   *
   * @var string[]
   */
  protected $saveLater = [];

  /**
   * Constructs an EntityCreate object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->storage = $storage;
    $this->componentId = $plugin_definition['component_id'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('rules_component')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $rules_config = $this->storage->load($this->componentId);

    // Setup an isolated execution state for this expression and pass on the
    // necessary context.
    $rules_component = $rules_config->getComponent();
    foreach ($this->getContexts() as $context_name => $context) {
      // Pass through the already existing typed data objects to avoid creating
      // them from scratch.
      $rules_component->getState()->setVariableData($context_name, $context->getContextData());
    }

    // We don't use RulesComponent::execute() here since we don't want to
    // auto-save immediately.
    $state = $rules_component->getState();
    $expression = $rules_component->getExpression();
    $expression->executeWithState($state);

    // Postpone auto-saving to the parent expression triggering this action.
    foreach ($state->getAutoSaveSelectors() as $selector) {
      $parts = explode('.', $selector);
      $context_name = reset($parts);
      if (array_key_exists($context_name, $this->context)) {
        $this->saveLater[] = $context_name;
      }
      else {
        // Otherwise we need to save here since it will not happen in the parent
        // execution.
        $typed_data = $state->fetchDataByPropertyPath($selector);
        // Things that can be saved must have a save() method, right?
        // Saving is always done at the root of the typed data tree, for example
        // on the entity level.
        $typed_data->getRoot()->getValue()->save();
      }
    }

    foreach ($this->getProvidedContextDefinitions() as $name => $definition) {
      $this->setProvidedValue($name, $state->getVariable($name));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function autoSaveContext() {
    return $this->saveLater;
  }

}
