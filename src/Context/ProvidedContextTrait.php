<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ProvidedContextTrait.
 */

namespace Drupal\rules\Context;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Plugin\Context\Context;

/**
 * Offers common methods for ProvidedContextPlugininterface implementors.
 */
trait ProvidedContextTrait {

  /**
   * The data objects that are provided by this plugin.
   *
   * @var \Drupal\Component\Plugin\Context\ContextInterface[]
   */
  protected $provided;

  /**
   * @see \Drupal\rules\Context\ProvidedContextPlugininterface
   */
  public function setProvidedValue($name, $value) {
    $this->getProvided($name)->setContextValue($value);
    return $this;
  }

  /**
   * @see \Drupal\rules\Context\ProvidedContextPlugininterface
   */
  public function getProvided($name) {
    // Check for a valid context value.
    if (!isset($this->provided[$name])) {
      $this->provided[$name] = new Context($this->getProvidedDefinition($name));
    }
    return $this->provided[$name];
  }

  /**
   * @see \Drupal\rules\Context\ProvidedContextPlugininterface
   */
  public function getProvidedDefinition($name) {
    $definition = $this->getPluginDefinition();
    if (empty($definition['provides'][$name])) {
      throw new ContextException(sprintf("The %s provided context is not valid.", $name));
    }
    return $definition['provides'][$name];
  }

  /**
   * @see \Drupal\rules\Context\ProvidedContextPlugininterface
   */
  public function getProvidedDefinitions() {
    $definition = $this->getPluginDefinition();
    return !empty($definition['provides']) ? $definition['provides'] : array();
  }

}
