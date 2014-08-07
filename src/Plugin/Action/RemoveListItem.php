<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\RemoveListItem.
 */

namespace Drupal\rules\Plugin\Action;

/**
 * Provides a 'Remove item from list' action.
 *
 * @Action(
 *   id = "rules_list_item_remove",
 *   label = @Translation("Remove item from list"),
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
 */
class RemoveListItem extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Remove list item');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $list = $this->getContextValue('list');
    $item = $this->getContextValue('item');

    foreach (array_keys($list, $item) as $key) {
      unset($list[$key]);
    }

    $this->setContextValue('list', $list)
  }
}
