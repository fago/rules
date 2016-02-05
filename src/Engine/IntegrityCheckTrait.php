<?php

/**
 * @file
 * Contains \Drupal\rules\Engine\IntegrityCheckTrait.
 */

namespace Drupal\rules\Engine;

use Drupal\Core\Plugin\Context\ContextDefinitionInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface as CoreContextAwarePluginInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\rules\Context\ContextDefinitionInterface as RulesContextDefinitionInterface;
use Drupal\rules\Context\ContextProviderInterface;
use Drupal\rules\Exception\RulesIntegrityException;

/**
 * Provides shared integrity checking methods for conditions and actions.
 */
trait IntegrityCheckTrait {

  /**
   * Performs the integrity check.
   *
   * @param CoreContextAwarePluginInterface $plugin
   *   The plugin with its defined context.
   * @param \Drupal\rules\Engine\ExecutionMetadataStateInterface $metadata_state
   *   The current configuration state with all defined variables that are
   *   available.
   *
   * @return \Drupal\rules\Engine\IntegrityViolationList
   *   The list of integrity violations.
   */
  protected function doCheckIntegrity(CoreContextAwarePluginInterface $plugin, ExecutionMetadataStateInterface $metadata_state) {
    $violation_list = new IntegrityViolationList();
    $context_definitions = $plugin->getContextDefinitions();

    foreach ($context_definitions as $name => $context_definition) {
      // Check if a data selector is configured that maps to the state.
      if (isset($this->configuration['context_mapping'][$name])) {
        try {
          $data_definition = $metadata_state->fetchDefinitionByPropertyPath($this->configuration['context_mapping'][$name]);

          $this->checkDataTypeCompatible($context_definition, $data_definition, $name, $violation_list);
        }
        catch (RulesIntegrityException $e) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('Data selector %selector for context %context_name is invalid. @message', [
            '%selector' => $this->configuration['context_mapping'][$name],
            '%context_name' => $context_definition->getLabel(),
            '@message' => $e->getMessage(),
          ]));
          $violation->setContextName($name);
          $violation->setUuid($this->getUuid());
          $violation_list->add($violation);
        }

        if ($context_definition instanceof RulesContextDefinitionInterface
          && $context_definition->getAssignmentRestriction() === RulesContextDefinitionInterface::ASSIGNMENT_RESTRICTION_INPUT
        ) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('The context %context_name may not be configured using a selector.', [
            '%context_name' => $context_definition->getLabel(),
          ]));
          $violation->setContextName($name);
          $violation->setUuid($this->getUuid());
          $violation_list->add($violation);
        }
      }
      elseif (isset($this->configuration['context_values'][$name])) {
        if ($context_definition instanceof RulesContextDefinitionInterface
          && $context_definition->getAssignmentRestriction() === RulesContextDefinitionInterface::ASSIGNMENT_RESTRICTION_SELECTOR
        ) {
          $violation = new IntegrityViolation();
          $violation->setMessage($this->t('The context %context_name may only be configured using a selector.', [
            '%context_name' => $context_definition->getLabel(),
          ]));
          $violation->setContextName($name);
          $violation->setUuid($this->getUuid());
          $violation_list->add($violation);
        }
      }
      elseif ($context_definition->isRequired()) {
        $violation = new IntegrityViolation();
        $violation->setMessage($this->t('The required context %context_name is missing.', [
          '%context_name' => $context_definition->getLabel(),
        ]));
        $violation->setContextName($name);
        $violation->setUuid($this->getUuid());
        $violation_list->add($violation);
      }
    }

    if ($plugin instanceof ContextProviderInterface) {
      $provided_context_definitions = $plugin->getProvidedContextDefinitions();

      foreach ($provided_context_definitions as $name => $context_definition) {
        if (isset($this->configuration['provides_mapping'][$name])) {
          if (!preg_match('/^[0-9a-zA-Z_]*$/', $this->configuration['provides_mapping'][$name])) {
            $violation = new IntegrityViolation();
            $violation->setMessage($this->t('Provided variable name %name contains not allowed characters.', [
              '%name' => $this->configuration['provides_mapping'][$name],
            ]));
            $violation->setContextName($name);
            $violation->setUuid($this->getUuid());
            $violation_list->add($violation);
          }

          // Populate the state with the new variable that is provided by this
          // plugin. That is necessary so that the integrity check in subsequent
          // actions knows about the variable and does not throw violations.
          $metadata_state->setDataDefinition(
            $this->configuration['provides_mapping'][$name],
            $context_definition->getDataDefinition()
          );
        }
        else {
          $metadata_state->setDataDefinition($name, $context_definition->getDataDefinition());
        }
      }
    }

    return $violation_list;
  }

  /**
   * Checks that the data type of a mapped variable matches the expectation.
   *
   * @param \Drupal\Core\Plugin\Context\ContextDefinitionInterface $context_definition
   *   The context definition of the context on the plugin.
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $provided
   *   The data definition of the mapped variable to the context.
   * @param string $context_name
   *   The name of the context on the plugin.
   * @param \Drupal\rules\Engine\IntegrityViolationList $violation_list
   *   The list of violations where new ones will be added.
   */
  protected function checkDataTypeCompatible(ContextDefinitionInterface $context_definition, DataDefinitionInterface $provided, $context_name, IntegrityViolationList $violation_list) {
    $expected_class = $context_definition->getDataDefinition()->getClass();
    $provided_class = $provided->getClass();
    $expected_type_problem = NULL;

    if (is_subclass_of($expected_class, PrimitiveInterface::class)
      && !is_subclass_of($provided_class, PrimitiveInterface::class)
    ) {
      $expected_type_problem = $this->t('primitive');
    }
    elseif (is_subclass_of($expected_class, ListInterface::class)
      && !is_subclass_of($provided_class, ListInterface::class)
    ) {
      $expected_type_problem = $this->t('list');
    }
    elseif (is_subclass_of($expected_class, ComplexDataInterface::class)
      && !is_subclass_of($provided_class, ComplexDataInterface::class)
    ) {
      $expected_type_problem = $this->t('complex');
    }

    if ($expected_type_problem) {
      $violation = new IntegrityViolation();
      $violation->setMessage($this->t('Expected a @expected_type data type for context %context_name but got a @provided_type data type instead.', [
        '@expected_type' => $expected_type_problem,
        '%context_name' => $context_definition->getLabel(),
        '@provided_type' => $provided->getDataType(),
      ]));
      $violation->setContextName($context_name);
      $violation->setUuid($this->getUuid());
      $violation_list->add($violation);
    }
  }

}
