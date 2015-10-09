<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesAction\UserRoleRemove.
 */

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;

/**
 * Provides a 'Remove user role' action.
 *
 * @RulesAction(
 *   id = "rules_user_role_remove",
 *   label = @Translation("Remove user role"),
 *   category = @Translation("User"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User")
 *     ),
 *     "roles" = @ContextDefinition("entity:user_role",
 *       label = @Translation("Roles"),
 *       multiple = TRUE
 *     )
 *   }
 * )
 */
class UserRoleRemove extends RulesActionBase {

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

    /** @var \Drupal\user\Entity\User $account */
    $account = $this->getContextValue('user');

    /** @var \Drupal\user\RoleInterface $roles */
    $roles = $this->getContextValue('roles');
    foreach ($roles as $role) {
      // Check if user has role.
      if ($account->hasRole($role->id())) {
        // If you try to add anonymous or authenticated role to user, Drupal
        // will throw an \InvalidArgumentException. Anonymous or authenticated
        // role ID must not be assigned manually.
        $account->removeRole($role->id());
        // Set flag that indicates if the entity should be auto-saved later.
        $this->saveLater = TRUE;
      }
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
