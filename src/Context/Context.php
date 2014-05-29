<?php

/**
 * @file
 * Contains \Drupal\rules\Context\Context.
 */

namespace Drupal\rules\Context;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\TypedData\TypedDataInterface;

/**
 * A context class for Rules.
 */
class Context implements ContextInterface {

  /**
   * The data associated with the context.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface
   */
  protected $contextData;

  /**
   * The definition to which a context must conform.
   *
   * @var \Drupal\rules\Context\ContextDefinitionInterface
   */
  protected $contextDefinition;

  /**
   * Sets the contextDefinition for us without needing to call the setter.
   */
  public function __construct(ContextDefinitionInterface $context_definition) {
    $this->contextDefinition = $context_definition;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextValue() {
    // Special case entities.
    // @todo: Remove once entities do not implemented TypedDataInterface any
    // more.
    if ($this->contextData instanceof ContentEntityInterface) {
      return $this->contextData;
    }
    return $this->contextData->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function setContextValue($value) {
    if ($value instanceof TypedDataInterface) {
      return $this->setContextData($value);
    }
    else {
      return $this->setContextData(\Drupal::typedDataManager()->create($this->contextDefinition->getDataDefinition(), $value));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getContextData() {
    return $this->getContextData;
  }

  /**
   * {@inheritdoc}
   */
  public function setContextData(TypedDataInterface $data) {
    $this->contextData = $data;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextDefinition() {
    return $this->contextDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function setContextDefinition(array $context_definition) {
    $this->contextDefinition = $context_definition;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    return $this->contextDefinition->getConstraints();
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    return $this->getContextData()->validate();
  }

}
