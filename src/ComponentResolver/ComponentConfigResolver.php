<?php

namespace Drupal\rules\ComponentResolver;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rules\Engine\RulesComponentResolverInterface;
use Drupal\rules\Entity\RulesComponentConfig;

/**
 * Resolves Rules component configs.
 */
class ComponentConfigResolver implements RulesComponentResolverInterface {

  /**
   * The rules component entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityStorage = $entity_type_manager->getStorage('rules_component');
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $ids) {
    return array_map(function (RulesComponentConfig $config) {
      return $config->getComponent();
    }, $this->entityStorage->loadMultiple($ids));
  }

}
