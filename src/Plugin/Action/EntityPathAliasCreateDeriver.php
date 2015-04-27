<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\EntityPathAliasCreateDeriver.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives entity create plugin definitions based on content entity types.
 *
 * @see \Drupal\rules\Plugin\Action\EntityPathAliasCreate
 */
class EntityPathAliasCreateDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Creates a new EntityPathAliasCreateDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(EntityManagerInterface $entity_manager, TranslationInterface $string_translation) {
    $this->entityManager = $entity_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('entity.manager'), $container->get('string_translation'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->entityManager->getDefinitions() as $entity_type_id => $entity_type) {
      // Only allow content entities and ignore configuration entities.
      if (!$entity_type instanceof ContentEntityTypeInterface) {
        continue;
      }

      $this->derivatives["entity:$entity_type_id"] = [
        'label' => $this->t('Create @entity_type path alias', ['@entity_type' => $entity_type->getLowercaseLabel()]),
        'category' => $this->t('Path'),
        'entity_type_id' => $entity_type_id,
        'context' => [
          'entity' => new ContextDefinition("entity:$entity_type_id", $entity_type->getLabel(), $this->t('The @entity_type for which to create a path alias.', ['@entity_type' => $entity_type->getLowercaseLabel()])),
          'alias' => new ContextDefinition('string', $this->t('Path alias'), $this->t("Specify an alternative path by which the content can be accessed. For example, 'about' for an about page. Use a relative path and do not add a trailing slash.")),
        ],
        'provides' => [],
      ] + $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
