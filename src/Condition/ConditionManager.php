<?php

/**
 * @file
 * Contains \Drupal\rules\Condition\ConditionManager.
 */

namespace Drupal\rules\Condition;

use Drupal\Core\Condition\ConditionManager as CoreConditionManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\rules\Context\AnnotatedClassDiscovery;

/**
 * Extends the core condition manager to add in Rules' context improvements.
 */
class ConditionManager extends CoreConditionManager {

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!$this->discovery) {
      // Swap out the annotated class discovery used, so we can control the
      // annotation classes picked.
      $discovery = new AnnotatedClassDiscovery($this->subdir, $this->namespaces, $this->pluginDefinitionAnnotationName);
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($discovery);
    }
    return $this->discovery;
  }

}
