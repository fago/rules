<?php

namespace Drupal\rules\Ui;

use Drupal\rules\Engine\RulesComponent;

/**
 * Interface for objects providing components for editing.
 *
 * Usually, this would be implemented by a config entity storing the component.
 */
interface RulesUiComponentProviderInterface {

  /**
   * Gets the Rules component to be edited.
   *
   * @return \Drupal\rules\Engine\RulesComponent
   *   The Rules component.
   */
  public function getComponent();

  /**
   * Updates the configuration based upon the given component.
   *
   * @param \Drupal\rules\Engine\RulesComponent $component
   *   The component containing the configuration to set.
   *
   * @return $this
   */
  public function updateFromComponent(RulesComponent $component);

}
