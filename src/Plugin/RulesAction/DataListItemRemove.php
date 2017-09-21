<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'Remove item from list' action.
 *
 * @RulesAction(
 *   id = "rules_list_item_remove",
 *   label = @Translation("Remove item from list"),
 *   category = @Translation("Data"),
 *   context = {
 *    "list" = @ContextDefinition("list",
 *      label = @Translation("List"),
 *      description = @Translation("The data list for which an item is to be removed.")
 *    ),
 *    "item" = @ContextDefinition("any",
 *      label = @Translation("Item"),
 *      description = @Translation("Item to remove.")
 *    ),
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class DataListItemRemove extends RulesActionBase {

  /**
   * Removes an item from a list.
   *
   * @param array $list
   *   An array to remove an item from.
   * @param mixed $item
   *   An item to remove from the array.
   */
  protected function doExecute(array $list, $item) {
    foreach (array_keys($list, $item) as $key) {
      unset($list[$key]);
    }

    $this->setContextValue('list', $list);
  }

}
