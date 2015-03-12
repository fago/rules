<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\UserIsBlocked.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides a 'User is blocked' condition.
 *
 * @Condition(
 *   id = "rules_user_is_blocked",
 *   label = @Translation("User is blocked"),
 *   category = @Translation("User"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class UserIsBlocked extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('User is blocked');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $account = $this->getContextValue('user');
    return $account->isBlocked();
  }

}
