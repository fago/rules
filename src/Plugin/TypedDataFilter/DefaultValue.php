<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\TypedDataFilter\DefaultFilter
 */

namespace Drupal\rules\Plugin\TypedDataFilter;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\rules\TypedData\DataFilterBase;

/**
 * A data filter lowering all string characters.
 *
 * @DataFilter(
 *   id = "default",
 *   label = @Translation("Applies a default-value if there is no value."),
 *   allowNull = true,
 * )
 */
class DefaultFilter extends DataFilterBase {

  /**
   * {@inheritdoc}
   */
  public function filter(DataDefinitionInterface $definition, $value, array $arguments) {
    return isset($value) ? $value : $arguments[0];
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
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function getNumberOfRequiredArguments() {
    return 1;
  }

  /**
   * {@inheritdoc}
   */
  public function validateArguments(DataDefinitionInterface $definition, array $arguments) {
    // Ensure the provided value is given for this data.
    $violations = $this->getTypedDataManager()
      ->create($definition, $arguments[0])
      ->validate();
    $return = [];
    foreach ($violations as $violation) {
      $return[] = $violation->getMessage();
    }
    return $return;
  }

}
