<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\TypedDataFilter\LowerFilter
 */

namespace Drupal\rules\Plugin\TypedDataFilter;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\Type\StringInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\rules\TypedData\DataFilterBase;

/**
 * A data filter lowering all string characters.
 *
 * @DataFilter(
 *   id = "lower",
 *   label = @Translation("Convert to lower-case")
 * )
 */
class LowerFilter extends DataFilterBase {

  /**
   * {@inheritdoc}
   */
  public function filter(DataDefinitionInterface $definition, $value, array $arguments) {
    return strtolower($value);
  }

  /**
   * {@inheritdoc}
   */
  public function canFilter(DataDefinitionInterface $definition) {
    return $definition->getClass() instanceof StringInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function filtersTo(DataDefinitionInterface $definition, array $arguments) {
    return DataDefinition::create('string');
  }

}
