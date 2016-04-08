<?php

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Core\RulesConditionBase;
use Drupal\user\UserInterface;

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
   * Check if user is blocked.
   *
   * @param \Drupal\user\UserInterface $account
   *   The account to check.
   *
   * @return bool
   *   TRUE if the account is blocked.
   */
  protected function doEvaluate(UserInterface $account) {
    return $account->isBlocked();
  }

}
