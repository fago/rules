<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\UserHasRole.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'User has roles(s)' condition.
 *
 * @Condition(
 *   id = "rules_user_has_role",
 *   label = @Translation("User has role(s)"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User")
 *     ),
 *     "roles" = @ContextDefinition("entity:role",
 *       label = @Translation("Entity"),
 *       multiple = TRUE
 *     ),
 *     "operation" = @ContextDefinition("string",
 *       label = @Translation("Match roles"),
 *       description = @Translation("If matching against all selected roles, the user must have <em>all</em> the roles selected."),
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class UserHasRole extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('User has role(s)');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $account = $this->getContextValue('user');
    $roles = $this->getContextValue('roles');
    $operation = $this->getContext('operation')->getContextData() ? $this->getContextValue('operation') : 'AND';

    $rids = array_map(function ($role) {
      return $role->id();
    }, $roles);

    switch ($operation) {
      case 'OR':
        return (bool) array_intersect($rids, $account->getRoles());

      case 'AND':
        return (bool) !array_diff($rids, $account->getRoles());
    }
    return FALSE;
  }

}
