<?php

/**
 * @file
 * Contains Drupal\rules\Entity\View.
 */

namespace Drupal\rules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Rules component configuration entity to persistently store configuration.
 *
 * @ConfigEntityType(
 *   id = "rules_component",
 *   label = @Translation("Rules component"),
 *   admin_permission = "administer rules",
 *   config_prefix = "component",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   }
 * )
 */
class RulesComponent extends ConfigEntityBase {

  /**
   * The unique ID of the Rules component.
   *
   * @var string
   */
  public $id = NULL;

  /**
   * The label of the Rules component.
   *
   * @var string
   */
  protected $label;

  /**
   * The description of the rule, which is used only in the interface.
   *
   * @var string
   */
  protected $description = '';

  /**
   * The "tags" of a Rules component.
   *
   * The tags are stored as a single string, though it is used as multiple tags
   * for example in the rules overview.
   *
   * @var string
   */
  protected $tag = '';

  /**
   * The core version the Rules component was created for.
   *
   * @var int
   */
  protected $core = \Drupal::CORE_COMPATIBILITY;

  /**
   * The Rules expression plugin ID that the configuration is for.
   *
   * @var string
   */
  protected $expression_id;

  /**
   * The expression plugin specific configuration as nested array.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * Stores a reference to the executable expression version of this component.
   *
   * @var \Drupal\rules\ViewExecutable
   */
  protected $expression;

  /**
   * The module implementing this Rules component.
   *
   * @var string
   */
  protected $module = 'rules';

  /**
   * Gets a Rules expression instance for this Rules component.
   *
   * @return \Drupal\rules\Engine\RulesExpressionInterface
   *   A Rules expression instance.
   */
  public function getExpression() {
    // Ensure that an executable Rules expression is available.
    if (!isset($this->expression)) {
      $this->expression = $this->getExpressionManager()->createInstance($this->expression_id, $this->configuration);
    }

    return $this->expression;
  }

  /**
   * Returns the Rules expression manager.
   *
   * @todo Actually we should use dependency injection here, but is that even
   *   possible with config entities? How?
   *
   * @return \Drupal\rules\Plugin\RulesExpressionPluginManager
   *   The Rules expression manager.
   */
  protected function getExpressionManager() {
    return \Drupal::service('plugin.manager.rules_expression');
  }

  /**
   * {@inheritdoc}
   */
  public function createDuplicate() {
    $duplicate = parent::createDuplicate();
    unset($duplicate->expression);
    return $duplicate;
  }

  /**
   * Overrides \Drupal\Core\Entity\Entity::label().
   *
   * When a certain component doesn't have a label return the ID.
   */
  public function label() {
    if (!$label = $this->get('label')) {
      $label = $this->id();
    }
    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // Ensure that the Rules component is dependant on the module that
    // implements the component.
    $this->addDependency('module', $this->module);

    // @todo Handle dependencies of plugins that are provided by various modules
    //   here.

    return $this->dependencies;
  }

}
