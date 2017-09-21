<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\rules\Engine\ExpressionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives Rules component action plugin definitions from config entities.
 *
 * @see RulesComponentAction
 */
class RulesComponentActionDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * The config entity storage that holds Rules components.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The Rules expression manager.
   *
   * @var \Drupal\rules\Engine\ExpressionManagerInterface
   */
  protected $expressionManager;

  /**
   * Contructor.
   */
  public function __construct(EntityStorageInterface $storage, ExpressionManagerInterface $expression_manager) {
    $this->storage = $storage;
    $this->expressionManager = $expression_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')->getStorage('rules_component'),
      $container->get('plugin.manager.rules_expression')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $rules_components = $this->storage->loadMultiple();
    foreach ($rules_components as $rules_component) {

      $component_config = $rules_component->get('component');
      $expression_definition = $this->expressionManager->getDefinition($component_config['expression']['id']);

      $this->derivatives[$rules_component->id()] = [
        'label' => $this->t('@expression_type: @label', [
          '@expression_type' => $expression_definition['label'],
          '@label' => $rules_component->label(),
        ]),
        'category' => $this->t('Components'),
        'component_id' => $rules_component->id(),
        'context' => $rules_component->getContextDefinitions(),
        'provides' => $rules_component->getProvidedContextDefinitions(),
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
