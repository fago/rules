<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\TypedDataFilter\LowerFilter.
 */

namespace Drupal\rules\Plugin\TypedDataFilter;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\Type\StringInterface;
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
  public function filter(DataDefinitionInterface $definition, $value, array $arguments, BubbleableMetadata $bubbleable_metadata = NULL) {
    return strtolower($value);
  }

  /**
   * {@inheritdoc}
   */
  public function canFilter(DataDefinitionInterface $definition) {
    return is_subclass_of($definition->getClass(), StringInterface::class);
  }

  /**
   * {@inheritdoc}
   */
  public function filtersTo(DataDefinitionInterface $definition, array $arguments) {
    return DataDefinition::create('string');
  }

}
