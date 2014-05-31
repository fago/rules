<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextAwarePluginBase.
 */

namespace Drupal\rules\Context;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Base class for plugins that are context aware.
 */
abstract class ContextAwarePluginBase extends PluginBase implements ContextAwarePluginInterface {

  /**
   * The contexts of this plugin.
   *
   * @var \Drupal\rules\Context\ContextInterface[]
   */
  protected $contexts;

  /**
   * The context definitions of this plugin.
   *
   * @var \Drupal\rules\Context\ContextDefinitionInterface[]
   */
  protected $contextDefinitions;

  /**
   * Defines the needed context of this plugin.
   *
   * @todo: Can we make this abstract somehow?
   *
   * @return \Drupal\rules\Context\ContextDefinitionInterface[]
   *   The array of context definitions, keyed by context name.
   */
  public static function contextDefinitions() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getContextDefinitions() {
    if (!isset($this->contextDefinitions)) {
      $this->contextDefinitions = static::contextDefinitions();
    }
    return $this->contextDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextDefinition($name) {
    $definitions = $this->getContextDefinitions();
    if (empty($definitions[$name])) {
      throw new ContextException("The $name context is not a valid context.");
    }
    return $definitions[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    // Make sure all context objects are initialized.
    foreach ($this->getContextDefinitions() as $name => $definition) {
      $this->getContext($name);
    }
    return $this->contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getContext($name) {
    // Check for a valid context value.
    if (!isset($this->context[$name])) {
      $this->context[$name] = new Context($this->getContextDefinition($name));
    }
    return $this->context[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function getContextValues() {
    $values = [];
    foreach ($this->getContextDefinitions() as $name => $definition) {
      $values[$name] = isset($this->context[$name]) ? $this->context[$name]->getContextValue() : NULL;
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextValue($name) {
    return $this->getContext($name)->getContextValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setContextValue($name, $value) {
    $this->getContext($name)->setContextValue($value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function validateContexts() {
    $violations = new ConstraintViolationList();
    // @todo: Implement symfony validator API to let the validator traverse
    // and set property paths accordingly.

    foreach ($this->getContexts() as $name => $context) {
      $violations->addAll($context->validate());
    }
    return $violations;
  }

}
