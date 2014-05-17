<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\UserIsBlocked.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;

/**
 * Provides a 'User is blocked' condition.
 *
 * @Condition(
 *   id = "rules_user_is_blocked",
 *   label = @Translation("User is blocked"),
 *   context = {
 *     "user" = {
 *       "label" = "The user",
 *       "type" = "entity:user"
 *     }
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class UserIsBlocked extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return t('User is blocked');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $account = $this->getContextValue('user');
    return $account->isBlocked();
  }

}
