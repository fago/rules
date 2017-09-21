<?php

namespace Drupal\rules\Engine;

use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedDataTrait;
use Drupal\rules\Context\ContextDefinitionInterface;
use Drupal\rules\Context\GlobalContextRepositoryTrait;
use Drupal\rules\Exception\EvaluationException;
use Drupal\rules\Exception\InvalidArgumentException;
use Drupal\typed_data\DataFetcherTrait;

/**
 * The rules execution state.
 *
 * A rule element may clone the state, so any added variables are only visible
 * for elements in the current PHP-variable-scope.
 */
class ExecutionState implements ExecutionStateInterface {

  use DataFetcherTrait;
  use GlobalContextRepositoryTrait;
  use TypedDataTrait;

  /**
   * Globally keeps the ids of rules blocked due to recursion prevention.
   *
   * @var array
   *
   * @todo Implement recursion prevention from D7.
   */
  static protected $blocked = [];

  /**
   * The known variables.
   *
   * @var \Drupal\Core\TypedData\TypedDataInterface[]
   */
  protected $variables = [];

  /**
   * Holds variables for auto-saving later.
   *
   * @var array
   */
  protected $saveLater = [];

  /**
   * Variable for saving currently blocked configs for serialization.
   *
   * @var array
   */
  protected $currentlyBlocked;

  /**
   * Creates the object.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface[] $variables
   *   (optional) Variables to initialize this state with.
   *
   * @return static
   */
  public static function create(array $variables = []) {
    return new static($variables);
  }

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\TypedData\TypedDataInterface[] $variables
   *   (optional) Variables to initialize this state with.
   */
  protected function __construct(array $variables) {
    $this->variables = $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariable($name, ContextDefinitionInterface $definition, $value) {
    $data = $this->getTypedDataManager()->create(
      $definition->getDataDefinition(),
      $value
    );
    $this->setVariableData($name, $data);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariableData($name, TypedDataInterface $data) {
    $this->variables[$name] = $data;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariable($name) {
    if (!$this->hasVariable($name)) {
      throw new EvaluationException("Unable to get variable $name, it is not defined.");
    }
    return $this->variables[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function getVariableValue($name) {
    return $this->getVariable($name)->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function hasVariable($name) {
    if (!array_key_exists($name, $this->variables)) {
      // If there is no such variable, lazy-add global context variables. That
      // way can safe time fetching global context if its not needed.
      if (!($name[0] === '@' && strpos($name, ':') !== FALSE)) {
        return FALSE;
      }
      $contexts = $this->getGlobalContextRepository()->getRuntimeContexts([$name]);
      if (!array_key_exists($name, $contexts)) {
        return FALSE;
      }
      $this->setVariableData($name, $contexts[$name]->getContextData());
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function removeVariable($name) {
    if (array_key_exists($name, $this->variables)) {
      unset($this->variables[$name]);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchDataByPropertyPath($property_path, $langcode = NULL) {
    try {
      // Support global context names as variable name by ignoring points in
      // the service name; e.g. @user.current_user_context:current_user.name.
      if ($property_path[0] == '@') {
        list($service, $property_path) = explode(':', $property_path, 2);
      }
      $parts = explode('.', $property_path);
      $var_name = array_shift($parts);
      if (isset($service)) {
        $var_name = $service . ':' . $var_name;
      }
      return $this
        ->getDataFetcher()
        ->fetchDataBySubPaths($this->getVariable($var_name), $parts, $langcode);
    }
    catch (InvalidArgumentException $e) {
      // Pass on the original exception in the exception trace.
      throw new EvaluationException($e->getMessage(), 0, $e);
    }
    catch (MissingDataException $e) {
      // Pass on the original exception in the exception trace.
      throw new EvaluationException($e->getMessage(), 0, $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function saveChangesLater($selector) {
    $this->saveLater[$selector] = TRUE;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAutoSaveSelectors() {
    return array_keys($this->saveLater);
  }

  /**
   * {@inheritdoc}
   */
  public function autoSave() {
    // Make changes permanent.
    foreach ($this->saveLater as $selector => $flag) {
      $typed_data = $this->fetchDataByPropertyPath($selector);
      // Things that can be saved must have a save() method, right?
      // Saving is always done at the root of the typed data tree, for example
      // on the entity level.
      $typed_data->getRoot()->getValue()->save();
    }
    return $this;
  }

}
