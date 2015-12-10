<?php

/**
 * @file
 * Contains Drupal\rules\TypedData\TypedDataManagerInterface
 */

namespace Drupal\rules\TypedData;

use Drupal\Core\TypedData\TypedDataManagerInterface as CoreTypedDataManagerInterface;

/**
 * Enhanced version of the core typed data manager interface.
 */
interface TypedDataManagerInterface extends CoreTypedDataManagerInterface {

  /**
   * Gets the data fetcher.
   *
   * @return \Drupal\rules\TypedData\DataFetcherInterface
   *   The data fetcher.
   */
  public function getDataFetcher();

}
