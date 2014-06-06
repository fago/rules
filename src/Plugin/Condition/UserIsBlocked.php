<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\UserIsBlocked.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesConditionBase;

/**
 * Provides a 'User is blocked' condition.
 *
 * @Condition(
 *   id = "rules_user_is_blocked",
 *   label = @Translation("User is blocked")
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 */
class UserIsBlocked extends RulesConditionBase {

  /**
   * {@inheritdoc}
   */
  public static function contextDefinitions(TypedDataManager $typed_data_manager) {
    $contexts['user'] = ContextDefinition::create($typed_data_manager, 'entity:user')
      ->setLabel(t('User'));

    return $contexts;
  }

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
