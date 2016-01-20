<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\TypedDataFilter\UrlFilter
 */

namespace Drupal\rules\Plugin\TypedDataFilter;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\rules\TypedData\DataFilterBase;

class UrlFilter extends DataFilterBase {

  /**
   * {@inheritdoc}
   */
  public function filter(DataDefinitionInterface $definition, $value, array $arguments) {
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function canFilter(DataDefinitionInterface $definition) {
    // TODO: Implement canFilter() method.
  }

  /**
   * {@inheritdoc}
   */
  public function filtersTo(DataDefinitionInterface $definition, array $arguments) {
    // TODO: Implement filtersTo() method.
  }

}
