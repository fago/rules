<?php

/**
 * @file
 * Contains \Drupal\rules\Core\RulesConditionBase.
 */

namespace Drupal\rules\Core;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\rules\Context\ContextProviderTrait;

/**
 * Base class for rules conditions.
 *
 * @todo Figure out whether buildConfigurationForm() is useful to Rules somehow.
 */
abstract class RulesConditionBase extends ConditionPluginBase implements RulesConditionInterface {

  use ContextProviderTrait;

  /**
   * {@inheritdoc}
   */
  public function negate($negate = TRUE) {
    $this->configuration['negate'] = $negate;
    return $this;
  }

}
