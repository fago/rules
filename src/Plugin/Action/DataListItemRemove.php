<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\DataListItemRemove.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\rules\Engine\RulesActionBase;

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
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class DataListItemRemove extends RulesActionBase {

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

    $this->setContextValue('list', $list);
  }
}
