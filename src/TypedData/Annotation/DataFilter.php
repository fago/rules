<?php

/**
 * @file
 * Contains \Drupal\rules\TypedData\Annotation\DataFilter.
 */

namespace Drupal\rules\TypedData\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Annotation class for data filter plugins.
 *
 * @Annotation
 */
class DataFilter extends Plugin {

  /**
   * The machine-name of the data filter.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the filter.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

}
