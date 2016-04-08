<?php

namespace Drupal\rules\Ui;

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
   * @return \Drupal\rules\Ui\RulesUiDefinition
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
   * Gets the component form, ready to be embedded in some other form.
   *
   * @return \Drupal\Core\Form\FormInterface
   *   The form object.
   */
  public function getForm();

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
   * @param array $options
   *   (optional) Options for generating the URL, as supported by
   *   \Drupal\Core\Url::fromRoute().
   *
   * @return \Drupal\Core\Url
   *   The URL of the base route.
   */
  public function getBaseRouteUrl(array $options = []);

  /**
   * Gets an URL for a Rules UI route.
   *
   * @param string $route_suffix
   *   The Rules UI route suffix that is appended to the base route. Supported
   *   routes are:
   *   - expression.add: The add expression form.
   *   - expression.edit: The edit expression form.
   *   - expression.delete: The delete expression form.
   *   - break.lock: The break lock form.
   * @param array $route_parameters
   *   (optional) An associative array of route parameter names and values.
   *   Depending on the route, the required parameters are:
   *   - expression-id: The expression plugin to add on expression.add.
   *   - uuid: The UUID of the expression to be edited or deleted.
   * @param array $options
   *   (optional) Options for generating the URL, as supported by
   *   \Drupal\Core\Url::fromRoute().
   *
   * @return \Drupal\Core\Url
   *   The URL of the given route.
   *
   * @see \Drupal\rules\Routing\RulesUiRouteSubscriber::registerRoutes()
   */
  public function getUrlFromRoute($route_suffix, array $route_parameters, array $options = []);

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
