<?php

/**
 * @file
 * Contains \Drupal\rules\Entity\Controller\RulesComponentListBuilder.
 */

namespace Drupal\rules\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;

/**
 * Defines a class to build a listing of RulesComponent entities.
 *
 * @see \Drupal\rules\Entity\RulesComponent
 */
class RulesComponentListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * We override ::render() so that we can add our own content above the table.
   * parent::render() is where EntityListBuilder creates the table using our
   * buildHeader() and buildRow() implementations.
   */
  public function render() {
    $build['description'] = [
      '#markup' => $this->t('These rules components are config entities. Add more text here.'),
    ];
    $build['table'] = parent::render();
    return $build;
  }

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
    /* @var $entity \Drupal\rules\Entity\RulesComponent */
    $row['id'] = $entity->id();
    $row['label'] = $this->getLabel($entity);
    // @todo: maybe link somewhere
    /* $entity->link($this->getLabel($entity)) */
    $row['description'] = $entity->getDescription();
    $row['tag'] = $entity->getTag();
    return $row + parent::buildRow($entity);
  }

}
