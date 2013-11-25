<?php

/**
 * @file
 * Contains \Drupal\rules\Annotation\RulesElement.
 */

namespace Drupal\rules\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Rules annotation object.
 *
 * @Annotation
 */
class RulesElement extends Plugin {

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

}
