<?php

namespace Drupal\rules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\rules\Rules;
use Drupal\rules\Ui\RulesUiComponentProviderInterface;
use Drupal\rules\Engine\ExpressionInterface;
use Drupal\rules\Engine\RulesComponent;

/**
 * Reaction rule configuration entity to persistently store configuration.
 *
 * @ConfigEntityType(
 *   id = "rules_reaction_rule",
 *   label = @Translation("Reaction Rule"),
 *   label_collection = @Translation("Reaction Rules"),
 *   label_singular = @Translation("reaction rule"),
 *   label_plural = @Translation("reaction rules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count reaction rule",
 *     plural = "@count reaction rules",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\rules\Entity\ReactionRuleStorage",
 *     "list_builder" = "Drupal\rules\Controller\RulesReactionListBuilder",
 *     "form" = {
 *        "add" = "\Drupal\rules\Form\ReactionRuleAddForm",
 *        "edit" = "\Drupal\rules\Form\ReactionRuleEditForm",
 *        "delete" = "\Drupal\Core\Entity\EntityDeleteForm"
 *      }
 *   },
 *   admin_permission = "administer rules",
 *   config_prefix = "reaction",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "events",
 *     "description",
 *     "tags",
 *     "config_version",
 *     "expression",
 *   },
 *   links = {
 *     "collection" = "/admin/config/workflow/rules",
 *     "edit-form" = "/admin/config/workflow/rules/reactions/edit/{rules_reaction_rule}",
 *     "delete-form" = "/admin/config/workflow/rules/reactions/delete/{rules_reaction_rule}",
 *     "break-lock-form" = "/admin/config/workflow/rules/reactions/edit/break-lock/{rules_reaction_rule}"
 *   }
 * )
 */
class ReactionRuleConfig extends ConfigEntityBase implements RulesUiComponentProviderInterface {

  /**
   * The unique ID of the Reaction Rule.
   *
   * @var string
   */
  public $id = NULL;

  /**
   * The label of the Reaction rule.
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
   * The "tags" of a Reaction rule.
   *
   * @var string[]
   */
  protected $tags = [];

  /**
   * The version the Reaction rule was created for.
   *
   * @var int
   */
  protected $config_version = Rules::CONFIG_VERSION;

  /**
   * The expression plugin specific configuration as nested array.
   *
   * @var array
   */
  protected $expression = [
    'id' => 'rules_rule',
  ];

  /**
   * Stores a reference to the executable expression version of this component.
   *
   * @var \Drupal\rules\Engine\ExpressionInterface
   */
  protected $expressionObject;

  /**
   * The events this reaction rule is reacting on.
   *
   * Events array. The array is numerically indexed and contains arrays with the
   * following structure:
   *   - event_name: String with the event machine name.
   *   - configuration: An array containing the event configuration.
   *
   * @var array
   */
  protected $events = [];

  /**
   * Sets a Rules expression instance for this Reaction rule.
   *
   * @param \Drupal\rules\Engine\ExpressionInterface $expression
   *   The expression to set.
   *
   * @return $this
   */
  public function setExpression(ExpressionInterface $expression) {
    $this->expressionObject = $expression;
    $this->expression = $expression->getConfiguration();
    return $this;
  }

  /**
   * Gets a Rules expression instance for this Reaction rule.
   *
   * @return \Drupal\rules\Engine\ExpressionInterface
   *   A Rules expression instance.
   */
  public function getExpression() {
    // Ensure that an executable Rules expression is available.
    if (!isset($this->expressionObject)) {
      $this->expressionObject = $this->getExpressionManager()->createInstance($this->expression['id'], $this->expression);
    }
    return $this->expressionObject;
  }

  /**
   * {@inheritdoc}
   *
   * Gets the Rules component that is invoked when the events are dispatched.
   * The returned component has the definitions of the available event context
   * set.
   */
  public function getComponent() {
    $component = RulesComponent::create($this->getExpression());
    $component->addContextDefinitionsForEvents($this->getEventNames());
    return $component;
  }

  /**
   * {@inheritdoc}
   */
  public function updateFromComponent(RulesComponent $component) {
    // Note that the available context definitions stem from the configured
    // events, which are handled separately.
    $this->setExpression($component->getExpression());
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
    unset($duplicate->expressionObject);
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
   * Gets configuration of all events the rule is reacting on.
   *
   * @return array
   *   The events array. The array is numerically indexed and contains arrays
   *   with the following structure:
   *     - event_name: String with the event machine name.
   *     - configuration: An array containing the event configuration.
   */
  public function getEvents() {
    return $this->events;
  }

  /**
   * Gets fully qualified names of all events the rule is reacting on.
   *
   * @return string[]
   *   The array of fully qualified event names of the rule.
   */
  public function getEventNames() {
    $names = [];
    foreach ($this->events as $event) {
      $names[] = $event['event_name'];
    }
    return $names;
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
    unset($this->expressionObject);
  }

}
