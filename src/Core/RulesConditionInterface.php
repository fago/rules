<?php

namespace Drupal\rules\Core;

use Drupal\rules\Context\ContextAwarePluginInterface;
use Drupal\Core\Condition\ConditionInterface;
use Drupal\rules\Context\ContextProviderInterface;

/**
 * Extends the core ConditionInterface to provide a negate() method.
 */
interface RulesConditionInterface extends ConditionInterface, ContextAwarePluginInterface, ContextProviderInterface, ConfigurationAccessControlInterface {

  /**
   * Negates the result after evaluating this condition.
   *
   * @param bool $negate
   *   TRUE to indicate that the condition should be negated, FALSE otherwise.
   *
   * @return $this
   */
  public function negate($negate = TRUE);

}
