<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesAction\UserUnblock.
 */

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

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
   * {@inheritdoc}
   */
  public function execute() {
    /** @var $user \Drupal\user\UserInterface */
    $user = $this->getContextValue('user');

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
