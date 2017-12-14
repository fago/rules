<?php

namespace Drupal\rules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Rules;
use Drupal\rules\Ui\RulesUiComponentProviderInterface;
use Drupal\rules\Engine\ExpressionInterface;
use Drupal\rules\Engine\RulesComponent;

/**
 * Rules component configuration entity to persistently store configuration.
 *
 * @ConfigEntityType(
 *   id = "rules_component",
 *   label = @Translation("Rules component"),
 *   label_collection = @Translation("Rules components"),
 *   label_singular = @Translation("rules component"),
 *   label_plural = @Translation("rules components"),
 *   label_count = @PluralTranslation(
 *     singular = "@count rules component",
 *     plural = "@count rules components",
 *   ),
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
 *     "description",
 *     "tags",
 *     "config_version",
 *     "component",
 *   },
 *   links = {
 *     "collection" = "/admin/config/workflow/rules/components",
 *     "edit-form" = "/admin/config/workflow/rules/components/edit/{rules_component}",
 *     "delete-form" = "/admin/config/workflow/rules/components/delete/{rules_component}",
 *   }
 * )
 */
class RulesComponentConfig extends ConfigEntityBase implements RulesUiComponentProviderInterface {

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
   * @var string[]
   */
  protected $tags = [];

  /**
   * The config version the Rules component was created for.
   *
   * @var int
   */
  protected $config_version = Rules::CONFIG_VERSION;

  /**
   * The component configuration as nested array.
   *
   * See \Drupal\rules\Engine\RulesComponent::getConfiguration()
   *
   * @var array
   */
  protected $component = [];

  /**
   * Stores a reference to the component object.
   *
   * @var \Drupal\rules\Engine\RulesComponent
   */
  protected $componentObject;

  /**
   * Gets a Rules expression instance for this Rules component.
   *
   * @return \Drupal\rules\Engine\ExpressionInterface
   *   A Rules expression instance.
   */
  public function getExpression() {
    return $this->getComponent()->getExpression();
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
    $this->component['expression'] = $expression->getConfiguration();
    unset($this->componentObject);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponent() {
    if (!isset($this->componentObject)) {
      $this->componentObject = RulesComponent::createFromConfiguration($this->component);
    }
    return $this->componentObject;
  }

  /**
   * {@inheritdoc}
   */
  public function updateFromComponent(RulesComponent $component) {
    $this->component = $component->getConfiguration();
    $this->componentObject = $component;
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
    if (!empty($this->component['context_definitions'])) {
      foreach ($this->component['context_definitions'] as $name => $definition) {
        $definitions[$name] = ContextDefinition::createFromArray($definition);
      }
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
  public function setContextDefinitions(array $definitions) {
    $this->component['context_definitions'] = [];
    foreach ($definitions as $name => $definition) {
      $this->component['context_definitions'][$name] = $definition->toArray();
    }
    return $this;
  }

  /**
   * Gets the definitions of the provided context.
   *
   * @return \Drupal\rules\Context\ContextDefinitionInterface[]
   *   The array of context definition, keyed by context name.
   */
  public function getProvidedContextDefinitions() {
    $definitions = [];
    if (!empty($this->component['provided_context_definitions'])) {
      foreach ($this->component['provided_context_definitions'] as $name => $definition) {
        $definitions[$name] = ContextDefinition::createFromArray($definition);
      }
    }
    return $definitions;
  }

  /**
   * Sets the definitions of the provided context.
   *
   * @param \Drupal\rules\Context\ContextDefinitionInterface[] $definitions
   *   The array of context definitions, keyed by context name.
   *
   * @return $this
   */
  public function setProvidedContextDefinitions(array $definitions) {
    $this->component['provided_context_definitions'] = [];
    foreach ($definitions as $name => $definition) {
      $this->component['provided_context_definitions'][$name] = $definition->toArray();
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function createDuplicate() {
    $duplicate = parent::createDuplicate();
    unset($duplicate->componentObject);
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
   * Returns the tags associated with this config.
   *
   * @return string[]
   *   The numerically indexed array of tag names.
   */
  public function getTags() {
    return $this->tags;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $this->addDependencies($this->getComponent()->calculateDependencies());
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
