<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\RulesAction\UserRoleAdd.
 */

namespace Drupal\rules\Plugin\RulesAction;

use Drupal\rules\Core\RulesActionBase;
use Drupal\user\UserInterface;

/**
 * Provides a 'Add user role' action.
 *
 * @RulesAction(
 *   id = "rules_user_role_add",
 *   label = @Translation("Add user role"),
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
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add port for rules_user_roles_options_list.
 */
class UserRoleAdd extends RulesActionBase {

  /**
   * Flag that indicates if the entity should be auto-saved later.
   *
   * @var bool
   */
  protected $saveLater = FALSE;

  /**
   * Assign role to a user.
   *
   * @param \Drupal\user\UserInterface $account
   *   User object.
   * @param \Drupal\user\RoleInterface[] $roles
   *   Array of UserRoles to assign.
   */
  protected function doExecute(UserInterface $account, array $roles) {
    foreach ($roles as $role) {
      // Skip adding the role to the user if they already have it.
      if (!$account->hasRole($role->id())) {
        // If you try to add anonymous or authenticated role to user, Drupal
        // will throw an \InvalidArgumentException. Anonymous or authenticated
        // role ID must not be assigned manually.
        $account->addRole($role->id());
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
