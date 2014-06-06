<?php

/**
 * @file
 * Contains \Drupal\rules\Context\ContextDefinition.
 */

namespace Drupal\rules\Context;

use Drupal\Core\TypedData\TypedDataManager;

/**
 * Defines a class for context definitions.
 */
class ContextDefinition implements ContextDefinitionInterface {

  /**
   * @var string
   */
  protected $dataType;

  /**
   * @var string
   */
  protected $label;

  /**
   * @var string
   */
  protected $description;

  /**
   * @var bool
   */
  protected $isMultiple = FALSE;

  /**
   * @var bool
   */
  protected $isRequired = TRUE;

  /**
   * @var array[]
   */
  protected $constraints = [];

  /**
   * The typed data manager used for creating the data types of the context.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * Creates a new context definition.
   *
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   * @param string $data_type
   *   The data type for which to create the context definition. Defaults to
   *   'any'.
   *
   * @return static
   *   The created context definition object.
   */
  public static function create(TypedDataManager $typed_data_manager, $data_type = 'any') {
    return new static(
      $typed_data_manager,
      $data_type
    );
  }

  /**
   * Constructs a new context definition object.
   *
   * @param \Drupal\Core\TypedData\TypedDataManager $typed_data_manager
   *   The typed data manager.
   * @param string $data_type
   *   The required data type.
   */
  public function __construct(TypedDataManager $typed_data_manager, $data_type = 'any') {
    $this->typedDataManager = $typed_data_manager;
    $this->dataType = $data_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataType() {
    return $this->dataType;
  }

  /**
   * {@inheritdoc}
   */
  public function setDataType($data_type) {
    $this->dataType = $data_type;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isMultiple() {
    return $this->isMultiple;
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple($multiple = TRUE) {
    $this->isMultiple = $multiple;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired() {
    return $this->isRequired;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequired($required = TRUE) {
    $this->isRequired = $required;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    // @todo: Apply defaults.
    return $this->constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraint($constraint_name) {
    $constraints = $this->getConstraints();
    return isset($constraints[$constraint_name]) ? $constraints[$constraint_name] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setConstraints(array $constraints) {
    $this->constraints = $constraints;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addConstraint($constraint_name, $options = NULL) {
    $this->constraints[$constraint_name] = $options;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataDefinition() {
    // @todo: Setters are missing from the core data definition interfaces.
    if ($this->isMultiple()) {
      $definition = $this->typedDataManager->createListDataDefinition($this->getDataType());
    }
    else {
      $definition = $this->typedDataManager->createDataDefinition($this->getDataType());
    }
    $definition->setLabel($this->getLabel())
      ->setDescription($this->getDescription())
      ->setRequired($this->isRequired())
      ->setConstraints($this->getConstraints());
    return $definition;
  }

}
