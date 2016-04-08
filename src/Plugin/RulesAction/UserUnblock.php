<?php

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;
use Drupal\user\UserInterface;

/**
 * Provides "Unblock User" action.
 *
 * @RulesAction(
 *   id = "rules_user_unblock",
 *   label = @Translation("Unblock a user"),
 *   category = @Translation("User"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User"),
 *       description = @Translation("Specifies the user, that should be unblocked.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 */
class UserUnblock extends RulesActionBase {
  /**
   * Flag that indicates if the entity should be auto-saved later.
   *
   * @var bool
   */
  protected $saveLater = FALSE;

  /**
   * Unblock a user.
   *
   * @param \Drupal\user\UserInterface $user
   *   The user to unblock.
   */
  protected function doExecute(UserInterface $user) {
    // Do nothing if user is anonymous or isn't blocked.
    if ($user->isAuthenticated() && $user->isBlocked()) {
      $user->activate();
      // Set flag that indicates if the entity should be auto-saved later.
      $this->saveLater = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function autoSaveContext() {
    if ($this->saveLater) {
      return ['user'];
    }
    return [];
  }

}
