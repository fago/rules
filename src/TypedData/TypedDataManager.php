<?php

/**
 * @file
 * Contains Drupal\rules\TypedData\TypedDataManager
 */

namespace Drupal\rules\TypedData;
/**
 * Enhanced version of the core typed data manager.
 */
class TypedDataManager extends \Drupal\Core\TypedData\TypedDataManager implements TypedDataManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function getDataFetcher() {
    return new DataFetcher();
  }

}
