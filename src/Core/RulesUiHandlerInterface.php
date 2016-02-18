<?php

/**
 * @file
 * Contains \Drupal\rules\Core\RulesUiHandlerInterface.
 */

namespace Drupal\rules\Core;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Engine\RulesComponent;

/**
 * Interface for Rules UI handlers.
 *
 * Rules UI handlers define where RulesUI instances are embedded and are
 * responsible for generating the appropriate routes.
 */
interface RulesUiHandlerInterface extends PluginInspectionInterface {

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\rules\Core\RulesUiDefinition
   *   The rules_ui plugin definition.
   */
  public function getPluginDefinition();

  /**
   * Gets the human-readable label of the component.
   *
   * The human-readable label used when referring to the whole component. This
   * can be a fixed string, or the label of a config entity.
   *
   * @return string
   *   The label.
   */
  public function getComponentLabel();

  /**
   * Gets the currently edited component.
   *
   * @return \Drupal\rules\Engine\RulesComponent
   *   The edited component.
   */
  public function getComponent();

  /**
   * Updates the edited component.
   *
   * @param \Drupal\rules\Engine\RulesComponent $component
   *   The updated, edited component.
   */
  public function updateComponent(RulesComponent $component);

  /**
   * Clears any temporary storage.
   *
   * Note that after clearing the temporary storage any unsaved changes are
   * lost.
   */
  public function clearTemporaryStorage();

  /**
   * Returns the URL of the base route, based on the current URL.
   *
   * @return \Drupal\Core\Url
   *   The url of the base route.
   */
  public function getBaseRouteUrl();

  /**
   * Determines if the component is locked for the current user.
   *
   * @return bool
   *   TRUE if locked, FALSE otherwise.
   */
  public function isLocked();

  /**
   * Checks if the rule has been modified and is present in the storage.
   *
   * @return bool
   *   TRUE if the rule has been modified, FALSE otherwise.
   */
  public function isEdited();

  /**
   * Provides information which user at which time locked the rule for editing.
   *
   * @return object
   *   StdClass object as provided by \Drupal\user\SharedTempStore.
   */
  public function getLockMetaData();

  /**
   * Renders a message if the rule component is locked/modified.
   *
   * @return array
   *   The render array, showing the message when applicable.
   */
  public function addLockInformation();

  /**
   * Validation callback that prevents editing locked rule components.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateLock(array &$form, FormStateInterface $form_state);

}
