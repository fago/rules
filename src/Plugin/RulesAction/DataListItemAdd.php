<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides an 'Add list item' action.
 *
 * @RulesAction(
 *   id = "rules_list_item_add",
 *   label = @Translation("Add list item"),
 *   category = @Translation("Data"),
 *   context = {
 *     "list" = @ContextDefinition("list",
 *       label = @Translation("List"),
 *       description = @Translation("The data list, to which an item is to be added.")
 *     ),
 *     "item" = @ContextDefinition("any",
 *       label = @Translation("Item"),
 *       description = @Translation("Item to add.")
 *     ),
 *     "unique" = @ContextDefinition("boolean",
 *       label = @Translation("Enforce uniqueness"),
 *       description = @Translation("Only add the item to the list if it is not yet contained."),
 *       default_value = FALSE,
 *       required = FALSE
 *     ),
 *     "pos" = @ContextDefinition("string",
 *       label = @Translation("Insert position"),
 *       description = @Translation("Position to insert the item."),
 *       default_value = "end",
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7?
 * @todo: set ContextDefinition restriction
 */
class DataListItemAdd extends RulesActionBase {

  /**
   * Add an item to a list.
   *
   * @param array $list
   *   A list to which an item is added.
   * @param mixed $item
   *   An item being added to the list.
   * @param bool $unique
   *   (optional) Whether or not we can add duplicate items.
   * @param string $position
   *   (optional) Determines if item will be added at beginning or end.
   *   Allowed values:
   *   - "start": Add to beginning of the list.
   *   - "end": Add to end of the list.
   */
  protected function doExecute(array $list, $item, $unique = FALSE, $position = 'end') {
    // Optionally, only add the list item if it is not yet contained.
    if (!((bool) $unique && in_array($item, $list))) {
      if ($position === 'start') {
        array_unshift($list, $item);
      }
      else {
        $list[] = $item;
      }
    }

    $this->setContextValue('list', $list);
  }

}
