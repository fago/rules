<?php

namespace Drupal\rules\Context;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Plugin\Context\Context;

/**
 * A trait implementing the ContextProviderInterface.
 *
 * This trait is intended for context aware plugins that want to provide
 * context.
 *
 * The trait requires the plugin to use configuration as defined by the
 * ContextConfig class.
 *
 * @see \Drupal\rules\Context\ContextProviderInterface
 */
trait ContextProviderTrait {

  /**
   * The data objects that are provided by this plugin.
   *
   * @var \Drupal\Component\Plugin\Context\ContextInterface[]
   */
  protected $providedContext;

  /**
   * @see \Drupal\rules\Context\ContextProviderInterface
   */
  public function setProvidedValue($name, $value) {
    $context = $this->getProvidedContext($name);
    $new_context = Context::createFromContext($context, $value);
    $this->providedContext[$name] = $new_context;
    return $this;
  }

  /**
   * @see \Drupal\rules\Context\ContextProviderInterface
   */
  public function getProvidedContext($name) {
    // Check for a valid context value.
    if (!isset($this->providedContext[$name])) {
      $this->providedContext[$name] = new Context($this->getProvidedContextDefinition($name));
    }
    return $this->providedContext[$name];
  }

  /**
   * @see \Drupal\rules\Context\ContextProviderInterface
   */
  public function getProvidedContextDefinition($name) {
    $definition = $this->getPluginDefinition();
    if (empty($definition['provides'][$name])) {
      throw new ContextException(sprintf("The %s provided context is not valid.", $name));
    }
    return $definition['provides'][$name];
  }

  /**
   * @see \Drupal\rules\Context\ContextProviderInterface
   */
  public function getProvidedContextDefinitions() {
    $definition = $this->getPluginDefinition();
    return !empty($definition['provides']) ? $definition['provides'] : [];
  }

}
