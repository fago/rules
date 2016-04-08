<?php

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Context\DataProcessorManager;
use Drupal\rules\Core\ConditionManager;
use Drupal\rules\Engine\ConditionExpressionInterface;
use Drupal\rules\Engine\ExecutionMetadataStateInterface;
use Drupal\rules\Engine\ExecutionStateInterface;
use Drupal\rules\Engine\ExpressionBase;
use Drupal\rules\Engine\ExpressionInterface;
use Drupal\rules\Context\ContextHandlerIntegrityTrait;
use Drupal\rules\Engine\IntegrityViolationList;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an executable condition expression.
 *
 * This plugin is used to wrap condition plugins and is responsible to setup all
 * the context necessary, instantiate the condition plugin and to execute it.
 *
 * @RulesExpression(
 *   id = "rules_condition",
 *   label = @Translation("Condition"),
 *   form_class = "\Drupal\rules\Form\Expression\ConditionForm"
 * )
 */
class RulesCondition extends ExpressionBase implements ConditionExpressionInterface, ContainerFactoryPluginInterface {

  use ContextHandlerIntegrityTrait;

  /**
   * The condition manager used to instantiate the condition plugin.
   *
   * @var \Drupal\rules\Core\ConditionManager
   */
  protected $conditionManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   *   Contains the following entries:
   *   - condition_id: The condition plugin ID.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\rules\Core\ConditionManager $condition_manager
   *   The condition manager.
   * @param \Drupal\rules\Context\DataProcessorManager $processor_manager
   *   The data processor plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConditionManager $condition_manager, DataProcessorManager $processor_manager) {
    // Make sure defaults are applied.
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->conditionManager = $condition_manager;
    $this->processorManager = $processor_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.condition'),
      $container->get('plugin.manager.rules_data_processor')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      // Per default the result of this expression is not negated.
      'negate' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    // If the plugin id has been set already, keep it if not specified.
    if (isset($this->configuration['condition_id'])) {
      $configuration += [
        'condition_id' => $this->configuration['condition_id'],
      ];
    }
    return parent::setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(ExecutionStateInterface $state) {
    $condition = $this->conditionManager->createInstance($this->configuration['condition_id'], [
      'negate' => $this->configuration['negate'],
    ]);

    $this->prepareContext($condition, $state);
    $result = $condition->evaluate();

    // Now that the condition has been executed it can provide additional
    // context which we will have to pass back in the evaluation state.
    $this->addProvidedContext($condition, $state);

    if ($this->isNegated()) {
      $result = !$result;
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function negate($negate = TRUE) {
    $this->configuration['negate'] = $negate;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isNegated() {
    return !empty($this->configuration['negate']);
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    if (!empty($this->configuration['condition_id'])) {
      $definition = $this->conditionManager->getDefinition($this->configuration['condition_id']);
      return $this->t('Condition: @label', ['@label' => $definition['label']]);
    }
    return parent::getLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormHandler() {
    if (isset($this->pluginDefinition['form_class'])) {
      $class_name = $this->pluginDefinition['form_class'];
      return new $class_name($this, $this->conditionManager);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checkIntegrity(ExecutionMetadataStateInterface $metadata_state, $apply_assertions = TRUE) {
    $violation_list = new IntegrityViolationList();
    if (empty($this->configuration['condition_id'])) {
      $violation_list->addViolationWithMessage($this->t('Condition plugin ID is missing'), $this->getUuid());
      return $violation_list;
    }
    if (!$this->conditionManager->hasDefinition($this->configuration['condition_id'])) {
      $violation_list->addViolationWithMessage($this->t('Condition plugin %plugin_id does not exist', [
        '%plugin_id' => $this->configuration['condition_id'],
      ]), $this->getUuid());
      return $violation_list;
    }

    $condition = $this->conditionManager->createInstance($this->configuration['condition_id'], [
      'negate' => $this->configuration['negate'],
    ]);
    // Prepare and refine the context before checking integrity, such that any
    // context definition changes are respected while checking.
    $this->prepareContextWithMetadata($condition, $metadata_state);
    $result = $this->checkContextConfigIntegrity($condition, $metadata_state);
    $this->prepareExecutionMetadataState($metadata_state, NULL, $apply_assertions);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareExecutionMetadataState(ExecutionMetadataStateInterface $metadata_state, ExpressionInterface $until = NULL, $apply_assertions = TRUE) {
    if ($until && $this->getUuid() === $until->getUuid()) {
      return TRUE;
    }
    $condition = $this->conditionManager->createInstance($this->configuration['condition_id'], [
      'negate' => $this->configuration['negate'],
    ]);
    // Make sure to refine context first, such that possibly refined definitions
    // of provided context are respected.
    $this->prepareContextWithMetadata($condition, $metadata_state);
    $this->addProvidedContextDefinitions($condition, $metadata_state);
    if ($apply_assertions && !$this->isNegated()) {
      $this->assertMetadata($condition, $metadata_state);
    }
  }

}
