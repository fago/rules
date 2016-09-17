<?php

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
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
