<?php

namespace Drupal\rules\Plugin\RulesExpression;

use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\rules\Engine\ActionExpressionContainer;
use Drupal\rules\Engine\ExecutionMetadataStateInterface;
use Drupal\rules\Engine\ExecutionStateInterface;
use Drupal\rules\Engine\IntegrityViolationList;
use Drupal\rules\Exception\IntegrityException;

/**
 * Holds a set of actions that are executed over the iteration of a list.
 *
 * @RulesExpression(
 *   id = "rules_loop",
 *   label = @Translation("Loop")
 * )
 */
class RulesLoop extends ActionExpressionContainer {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      // Default to 'list_item' as variable name for the list item.
      'list_item' => 'list_item',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(ExecutionStateInterface $state) {
    $list_data = $state->fetchDataByPropertyPath($this->configuration['list']);
    $list_item_name = $this->configuration['list_item'];

    foreach ($list_data as $item) {
      $state->setVariableData($list_item_name, $item);
      foreach ($this->actions as $action) {
        $action->executeWithState($state);
      }
    }
    // After the loop the list item is out of scope and cannot be used by any
    // following actions.
    $state->removeVariable($list_item_name);
  }

  /**
   * {@inheritdoc}
   */
  public function checkIntegrity(ExecutionMetadataStateInterface $metadata_state, $apply_assertions = TRUE) {
    $violation_list = new IntegrityViolationList();

    if (empty($this->configuration['list'])) {
      $violation_list->addViolationWithMessage($this->t('List variable is missing.'));
      return $violation_list;
    }

    try {
      $list_definition = $metadata_state->fetchDefinitionByPropertyPath($this->configuration['list']);
    }
    catch (IntegrityException $e) {
      $violation_list->addViolationWithMessage($this->t('List variable %list does not exist. @message', [
        '%list' => $this->configuration['list'],
        '@message' => $e->getMessage(),
      ]));
      return $violation_list;
    }

    $list_item_name = isset($this->configuration['list_item']) ? $this->configuration['list_item'] : 'list_item';
    if ($metadata_state->hasDataDefinition($list_item_name)) {
      $violation_list->addViolationWithMessage($this->t('List item name %name conflicts with an existing variable.', [
        '%name' => $list_item_name,
      ]));
      return $violation_list;
    }

    if (!$list_definition instanceof ListDataDefinitionInterface) {
      $violation_list->addViolationWithMessage($this->t('The data type of list variable %list is not a list.', [
        '%list' => $this->configuration['list'],
      ]));
      return $violation_list;
    }

    // So far all ok, so continue with checking integrity in contained actions.
    // The parent implementation will take care of invoking pre/post traversal
    // metadata state preparations.
    $violation_list = parent::checkIntegrity($metadata_state, $apply_assertions);
    return $violation_list;
  }

  /**
   * {@inheritdoc}
   */
  protected function allowsMetadataAssertions() {
    // As the list can be empty, we cannot ensure child expressions are
    // executed at all - thus no assertions can be added.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareExecutionMetadataStateBeforeTraversal(ExecutionMetadataStateInterface $metadata_state) {
    try {
      $list_definition = $metadata_state->fetchDefinitionByPropertyPath($this->configuration['list']);
      $list_item_definition = $list_definition->getItemDefinition();
      $metadata_state->setDataDefinition($this->configuration['list_item'], $list_item_definition);
    }
    catch (IntegrityException $e) {
      // Silently eat the exception: we just continue without adding the list
      // item definition to the state.
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareExecutionMetadataStateAfterTraversal(ExecutionMetadataStateInterface $metadata_state) {
    // Remove the list item variable after the loop, it is out of scope now.
    $metadata_state->removeDataDefinition($this->configuration['list_item']);
  }

}
