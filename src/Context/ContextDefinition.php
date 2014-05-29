<?php
/**
 * @file
 * 
 */

namespace Drupal\rules\Context;

/**
 * Class ContextDefinition
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
  protected $isMultiple;

  /**
   * @var bool
   */
  protected $isRequired;

  /**
   * @var array[]
   */
  protected $constraints;

  /**
   * Creates a new context definition.
   *
   * @param string $data_type
   *
   */
  public static function create($data_type) {
    return new static($data_type);
  }

  /**
   * Constructs the object.
   *
   * @param string $data_type
   *   The required data type.
   */
  public function __construct($data_type = 'any') {
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
  public function getLabel() {
    return $this->label;
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
  public function isMultiple() {
    return $this->isMultiple;
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
  public function getDataDefinition() {
    // @todo: Setters are missing from the core data definition interfaces.
    if ($this->isMultiple()) {
      $definition = \Drupal::typedDataManager()->createListDataDefinition($this->getDataType());
    }
    else {
      $definition = \Drupal::typedDataManager()->createDataDefinition($this->getDataType());
    }
    $definition->setLabel($this->getLabel())
      ->setDescription($this->getDescription())
      ->setRequired($this->getRequired())
      ->setConstraints($this->getConstraints());
    return $definition;
  }

}
