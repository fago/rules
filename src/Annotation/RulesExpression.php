<?php

/**
 * @file
 * Contains \Drupal\rules\Annotation\RulesExpression.
 */

namespace Drupal\rules\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the RulesExpression annotation class.
 *
 * @Annotation
 */
class RulesExpression extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the rules plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The rules type, either "action" or "condition".
   *
   * @var string
   */
  public $type = '';

  /**
   * The class name of the form for displaying/editing this expression.
   *
   * @var string
   */
  public $form_class;

}
