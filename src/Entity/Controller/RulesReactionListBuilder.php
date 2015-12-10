<?php

/**
 * @file
 * Contains \Drupal\rules\Entity\Controller\RulesReactionListBuilder.
 */

namespace Drupal\rules\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;

/**
 * Defines a class to build a listing of ReactionRuleConfig entities.
 *
 * @see \Drupal\rules\Entity\ReactionRule
 */
class RulesReactionListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * Building the header and content lines for the contact list.
   *
   * Calling the parent::buildHeader() adds a column for the possible actions
   * and inserts the 'edit' and 'delete' links as defined for the entity type.
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['label'] = $this->t('Label');
    $header['description'] = $this->t('Description');
    $header['tag'] = $this->t('Tag');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\rules\Entity\ReactionRuleConfig */
    $row['id'] = $entity->id();
    $row['label'] = $this->getLabel($entity);
    $row['description'] = $entity->getDescription();
    $row['tag'] = $entity->getTag();
    return $row + parent::buildRow($entity);
  }

}
