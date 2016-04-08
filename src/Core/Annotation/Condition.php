<?php

namespace Drupal\rules\Core\Annotation;

use Drupal\Core\Condition\Annotation\Condition as CoreConditionAnnotation;

/**
 * Extension of the Condition annotation class.
 *
 * @Annotation
 *
 * This class adds a configuration access parameter to the Condition
 * annotation.
 */
class Condition extends CoreConditionAnnotation {

  /**
   * The permissions allowed to access the configuration UI for this plugin.
   *
   * @var string[]
   *   Array of permission strings as declared in a *.permissions.yml file. If
   *   any one of these permissions apply for the relevant user, we allow
   *   access.
   *
   *   The key should be used as follows. Note that we add a space between "@"
   *   and "Condition", since we do not want to trigger the annotation parser
   *   here; you should remove that space in your actual annotation:
   *
   *   @ Condition(
   *     id = "my_module_user_is_blocked",
   *     label = @Translation("My User is blocked"),
   *     category = @Translation("User"),
   *     context = {
   *       "user" = @ContextDefinition("entity:user",
   *         label = @Translation("User")
   *      ),
   *      configure_permissions = {
   *        "administer users",
   *        "block users"
   *      }
   *   }
   * )
   */
  public $configure_permissions;

}
