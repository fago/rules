<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\UserHasRole.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'User has roles(s)' condition.
 *
 * @Condition(
 *   id = "rules_user_has_role",
 *   label = @Translation("User has role(s)")
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class UserHasRole extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    $contexts['user'] = ContextDefinition::create($typed_data_manager, 'entity:user')
      ->setLabel(t('User'));

    $contexts['roles'] = ContextDefinition::create($typed_data_manager, 'user:roles')
      ->setLabel(t('Roles'));

    $contexts['operation'] = ContextDefinition::create($typed_data_manager, 'string')
      ->setLabel(t('Match roles'))
      ->setDescription(t('If matching against all selected roles, the user must have <em>all</em> the roles selected.'))
      ->setRequired(FALSE);

    return $contexts;
  }

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

    $rids = array_filter(array_map('trim', $roles));
    switch ($operation) {
      case 'OR':
        return (bool) array_intersect($rids, $account->getRoles());

      case 'AND':
        return (bool) !array_diff($rids, $account->getRoles());
    }
    return FALSE;
  }

}
