<?php

/**
 * @file
 * Contains \Drupal\rules\TypedData\TypedDataManagerTrait.
 */

namespace Drupal\rules\TypedData;

/**
 * Wrapper methods for classes that needs typed data manager object.
 */
trait TypedDataManagerTrait {

  /**
   * The typed data manager used for creating the data types.
   *
   * @var \Drupal\rules\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * Sets the typed data manager.
   *
   * @param \Drupal\rules\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   *
   * @return $this
   */
  public function setTypedDataManager(TypedDataManagerInterface $typed_data_manager) {
    $this->typedDataManager = $typed_data_manager;
    return $this;
  }

  /**
   * Gets the typed data manager.
   *
   * @return \Drupal\rules\TypedData\TypedDataManagerInterface
   *   The typed data manager.
   */
  public function getTypedDataManager() {
    if (empty($this->typedDataManager)) {
      $this->typedDataManager = \Drupal::typedDataManager();
    }

    return $this->typedDataManager;
  }

}
