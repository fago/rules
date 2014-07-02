<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\DataIsEmpty.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'Data value is empty' condition.
 *
 *  @Condition(
 *   id = "rules_data_is_empty",
 *   label = @Translation("Data value is empty"),
 *   context = {
 *     "data" = @ContextDefinition("any",
 *       label = @Translation("Data to check"),
 *       description = @Translation("The data to be checked to be empty, specified by using a data selector, e.g. 'node:uid:entity:name:value'.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class DataIsEmpty extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Data value is empty');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $data = $this->getContextValue('data');
    if ($data instanceof ComplexDataInterface || $data instanceof ListInterface) {
      return $data->isEmpty();
    }

    return empty($data);
  }

}
