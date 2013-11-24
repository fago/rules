<?php

/**
 * @file
 * Contains \Drupal\rules\Annotation\Rules.
 */

namespace Drupal\rules\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Rules annotation object.
 *
 * @Annotation
 */
class Rules extends Plugin {

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
