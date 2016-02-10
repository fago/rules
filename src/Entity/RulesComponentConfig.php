<?php

/**
 * @file
 * Contains Drupal\rules\Entity\RulesComponentConfig.
 */

namespace Drupal\rules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\ExpressionInterface;

/**
 * Rules component configuration entity to persistently store configuration.
 *
 * @ConfigEntityType(
 *   id = "rules_component",
 *   label = @Translation("Rules component"),
 *   handlers = {
 *     "list_builder" = "Drupal\rules\Controller\RulesComponentListBuilder",
 *     "form" = {
 *        "add" = "\Drupal\rules\Form\RulesComponentAddForm",
 *        "edit" = "\Drupal\rules\Form\RulesComponentEditForm",
 *        "delete" = "\Drupal\Core\Entity\EntityDeleteForm"
 *      }
 *   },
 *   admin_permission = "administer rules",
 *   config_prefix = "component",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "module",
 *     "description",
 *     "tag",
 *     "core",
 *     "expression_id",
 *     "context_definitions",
 *     "provided_context_definitions",
 *     "configuration",
 *   },
 *   links = {
 *     "collection" = "/admin/config/workflow/rules/components",
 *     "edit-form" = "/admin/config/workflow/rules/components/edit/{rules_component}",
 *     "delete-form" = "/admin/config/workflow/rules/components/delete/{rules_component}",
 *   }
 * )
 */
class RulesComponentConfig extends ConfigEntityBase {

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
   * Array of context definition arrays, keyed by context name.
   *
   * @var array[]
   */
  protected $context_definitions = [];

  /**
   * Array of provided context definition arrays, keyed by context name.
   *
   * @var array[]
   */
  protected $provided_context_definitions = [];

  /**
   * The expression plugin specific configuration as nested array.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * Stores a reference to the executable expression version of this component.
   *
   * @var \Drupal\rules\Engine\ExpressionInterface
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
   * @return \Drupal\rules\Engine\ExpressionInterface
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
   * Sets a Rules expression instance for this Rules component.
   *
   * @param \Drupal\rules\Engine\ExpressionInterface $expression
   *   The expression to set.
   *
   * @return $this
   */
  public function setExpression(ExpressionInterface $expression) {
    $this->expression = $expression;
    $this->expression_id = $expression->getPluginId();
    $this->configuration = $expression->getConfiguration();
    return $this;
  }

  /**
   * Gets the configured component.
   *
   * @return \Drupal\rules\Engine\RulesComponent
   *   The component.
   */
  public function getComponent() {
    $component = \Drupal\rules\Engine\RulesComponent::create($this->getExpression());
    foreach ($this->context_definitions as $name => $definition) {
      $component->addContextDefinition($name, ContextDefinition::createFromArray($definition));
    }
    foreach ($this->provided_context_definitions as $name => $definition) {
      $component->provideContext($name);
    }
    return $component;
  }

  /**
   * Sets the Rules component to be stored.
   *
   * @param \Drupal\rules\Engine\RulesComponent $component
   *   The component.
   *
   * @return $this
   */
  public function setComponent(\Drupal\rules\Engine\RulesComponent $component) {
    $this->setExpression($component->getExpression());
    $this->setContextDefinitions($component->getContextDefinitions());
    return $this;
  }

  /**
   * Gets the definitions of the used context.
   *
   * @return \Drupal\rules\Context\ContextDefinitionInterface[]
   *   The array of context definition, keyed by context name.
   */
  public function getContextDefinitions() {
    $definitions = [];
    foreach ($this->context_definitions as $name => $definition) {
      $definitions[$name] = ContextDefinition::createFromArray($definition);
    }
    return $definitions;
  }

  /**
   * Sets the definitions of the used context.
   *
   * @param \Drupal\rules\Context\ContextDefinitionInterface[] $definitions
   *   The array of context definitions, keyed by context name.
   *
   * @return $this
   */
  public function setContextDefinitions($definitions) {
    $this->context_definitions = [];
    foreach ($definitions as $name => $definition) {
      $this->context_definitions[$name] = $definition->toArray();
    }
    return $this;
  }

  /**
   * Gets the provided definitions of this component.
   *
   * @return \Drupal\rules\Context\ContextDefinitionInterface[]
   *   The array of provided context definitions, keyed by context name.
   */
  public function getProvidedContextDefinitions() {
    $definitions = [];
    foreach ($this->provided_context_definitions as $name => $definition) {
      $definitions[$name] = ContextDefinition::createFromArray($definition);
    }
    return $definitions;
  }

  /**
   * Sets the provided definitions of this component.
   *
   * @param \Drupal\rules\Context\ContextDefinitionInterface[] $definitions
   *   The array of provided context definitions, keyed by context name.
   *
   * @return $this
   */
  public function setProvidedContextDefinitions($definitions) {
    $this->provided_context_definitions = [];
    foreach ($definitions as $name => $definition) {
      $this->provided_context_definitions[$name] = $definition->toArray();
    }
    return $this;
  }

  /**
   * Returns the Rules expression manager.
   *
   * @todo Actually we should use dependency injection here, but is that even
   *   possible with config entities? How?
   *
   * @return \Drupal\rules\Engine\ExpressionManager
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
   * When a certain component does not have a label return the ID.
   */
  public function label() {
    if (!$label = $this->get('label')) {
      $label = $this->id();
    }
    return $label;
  }

  /**
   * Returns the description.
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Returns the tag.
   */
  public function getTag() {
    return $this->tag;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // Ensure that the Rules component is dependent on the module that
    // implements the component.
    $this->addDependency('module', $this->module);

    // @todo Handle dependencies of plugins that are provided by various modules
    //   here.
    return $this->dependencies;
  }

  /**
   * Magic clone method.
   */
  public function __clone() {
    // Remove the reference to the expression object in the clone so that the
    // expression object tree is created from scratch.
    unset($this->expression);
  }

}
