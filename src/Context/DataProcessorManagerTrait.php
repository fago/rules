<?php

namespace Drupal\rules\Context;

/**
 * Trait for easily using the data processor service.
 *
 * @see \Drupal\rules\Context\DataProcessorManager
 */
trait DataProcessorManagerTrait {

  /**
   * The data processor manager.
   *
   * @var \Drupal\rules\Context\DataProcessorManager
   */
  protected $dataProcessorManager;

  /**
   * Sets the data processor manager.
   *
   * @param \Drupal\rules\Context\DataProcessorManager $dataProcessorManager
   *   The data processor manager.
   *
   * @return $this
   */
  public function setDataProcessorManager(DataProcessorManager $dataProcessorManager) {
    $this->dataProcessorManager = $dataProcessorManager;
    return $this;
  }

  /**
   * Gets the data processor manager.
   *
   * @return \Drupal\rules\Context\DataProcessorManager
   *   The data processor manager.
   */
  public function getDataProcessorManager() {
    if (empty($this->dataProcessorManager)) {
      $this->dataProcessorManager = \Drupal::service('plugin.manager.rules_data_processor');
    }
    return $this->dataProcessorManager;
  }

}
