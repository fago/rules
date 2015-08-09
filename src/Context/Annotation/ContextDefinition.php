<?php

/**
 * @file
 * Contains \Drupal\rules\Context\Annotation\ContextDefinition.
 */

namespace Drupal\rules\Context\Annotation;

use \Drupal\Core\Annotation\ContextDefinition as CoreContextDefinition;

/**
 * Extends the core context definition annotation object for Rules.
 *
 * Ensures context definitions use
 * \Drupal\rules\Context\ContextDefinitionInterface.
 *
 * @Annotation
 *
 * @ingroup plugin_context
 */
class ContextDefinition extends CoreContextDefinition {

  /**
   * The ContextDefinitionInterface object.
   *
   * @var \Drupal\rules\Context\ContextDefinitionInterface.
   */
  protected $definition;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values) {
    if (isset($values['class']) && !in_array('Drupal\rules\Context\ContextDefinitionInterface', class_implements($values['class']))) {
      throw new \Exception('ContextDefinition class must implement \Drupal\rules\Context\ContextDefinitionInterface.');
    }
    // Default to Rules context definition class.
    $values['class'] = isset($values['class']) ? $values['class'] : '\Drupal\rules\Context\ContextDefinition';
    parent::__construct($values);
  }

  /**
   * Returns the value of an annotation.
   *
   * @return \Drupal\rules\Context\ContextDefinitionInterface.
   */
  public function get() {
    return $this->definition;
  }

}
