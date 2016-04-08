<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'Data set' action.
 *
 * @RulesAction(
 *   id = "rules_data_set",
 *   label = @Translation("Set a data value"),
 *   category = @Translation("Data"),
 *   context = {
 *     "data" = @ContextDefinition("any",
 *       label = @Translation("Data"),
 *       description = @Translation("Specifies the data to be modified using a data selector, e.g. 'node:author:name'."),
 *       allow_null = TRUE,
 *       assignment_restriction = "selector"
 *     ),
 *     "value" = @ContextDefinition("any",
 *       label = @Translation("Value"),
 *       description = @Translation("The new value to set for the specified data."),
 *       default_value = NULL,
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo Add various input restrictions: selector on 'data'.
 * @todo 'allow NULL' for both 'data' and 'value'?
 */
class DataSet extends RulesActionBase {

  /**
   * Executes the Plugin.
   *
   * @param mixed $data
   *   Original value of an element which is being updated.
   * @param mixed $value
   *   A new value which is being set to an element identified by data selector.
   */
  protected function doExecute($data, $value) {
    $typed_data = $this->getContext('data')->getContextData();
    $typed_data->setValue($value);
  }

  /**
   * {@inheritdoc}
   */
  public function autoSaveContext() {
    // Saving is done at the root of the typed data tree, for example on the
    // entity level.
    $typed_data = $this->getContext('data')->getContextData();
    $root = $typed_data->getRoot();
    $value = $root->getValue();
    // Only save things that are objects and have a save() method.
    if (is_object($value) && method_exists($value, 'save')) {
      return ['data'];
    }
    return [];
  }

}
