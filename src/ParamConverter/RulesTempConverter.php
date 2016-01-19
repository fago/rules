<?php

/**
 * @file
 * Contains \Drupal\rules\ParamConverter\RulesTempConverter.
 */

namespace Drupal\rules\ParamConverter;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\ParamConverter\EntityConverter;
use Symfony\Component\Routing\Route;
use Drupal\user\SharedTempStoreFactory;

/**
 * Provides upcasting for a rules entity to be used in the UI.
 *
 * Either loads the rule from the temporary storage if it is currently being
 * edited or from the canonical entity storage otherwise.
 *
 * Largely copied from \Drupal\views_ui\ParamConverter\ViewsUIConverter.
 */
class RulesTempConverter extends EntityConverter {

  /**
   * Stores the tempstore factory.
   *
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\user\SharedTempStoreFactory $temp_store_factory
   *   The factory for the temp store object.
   */
  public function __construct(EntityManagerInterface $entity_manager, SharedTempStoreFactory $temp_store_factory) {
    parent::__construct($entity_manager);

    $this->tempStoreFactory = $temp_store_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    // Standard upcasting: check if the config entity exists at all in the
    // storage.
    if (!$entity = parent::convert($value, $definition, $name, $defaults)) {
      return;
    }

    // Now check if there is also a version being edited and return that.
    $store = $this->tempStoreFactory->get($entity->getEntityTypeId());
    $edited_entity = $store->get($value);
    if ($edited_entity) {
      return $edited_entity;
    }

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    if (parent::applies($definition, $name, $route)) {
      return !empty($definition['tempstore']) && $definition['type'] === 'entity:rules_reaction_rule';
    }
    return FALSE;
  }

}
