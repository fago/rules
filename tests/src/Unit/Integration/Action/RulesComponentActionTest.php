<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\rules\Context\ContextConfig;
use Drupal\rules\Context\ContextDefinition;
use Drupal\rules\Engine\RulesComponent;
use Drupal\rules\Entity\RulesComponentConfig;
use Drupal\Tests\rules\Unit\Integration\RulesEntityIntegrationTestBase;

/**
 * Tests for exposing Rules components as action plugins.
 *
 * @group RulesAction
 */
class RulesComponentActionTest extends RulesEntityIntegrationTestBase {

  /**
   * Tests that a rule can be used as action.
   */
  public function testActionAvailable() {
    $rule = $this->rulesExpressionManager->createRule();

    $rules_config = new RulesComponentConfig([
      'id' => 'test_rule',
      'label' => 'Test rule',
    ], 'rules_component');
    $rules_config->setExpression($rule);

    $this->prophesizeStorage([$rules_config]);

    $definition = $this->actionManager->getDefinition('rules_component:test_rule');
    $this->assertEquals('Components', $definition['category']);
    $this->assertEquals('Rule: Test rule', (string) $definition['label']);
  }

  /**
   * Tests that the execution of the action invokes the Rules component.
   */
  public function testExecute() {
    // Set up a rules component that will just save an entity.
    $nested_rule = $this->rulesExpressionManager->createRule();
    $nested_rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'entity')
    );

    $rules_config = new RulesComponentConfig([
      'id' => 'test_rule',
      'label' => 'Test rule',
    ], 'rules_component');
    $rules_config->setExpression($nested_rule);
    $rules_config->setContextDefinitions(['entity' => ContextDefinition::create('entity')]);

    $this->prophesizeStorage([$rules_config]);

    // Invoke the rules component in another rule.
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_component:test_rule', ContextConfig::create()
      ->map('entity', 'entity')
    );

    // The call to save the entity means that the action was executed.
    $entity = $this->prophesizeEntity(EntityInterface::class);
    $entity->save()->shouldBeCalledTimes(1);

    RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->setContextValue('entity', $entity->reveal())
      ->execute();
  }

  /**
   * Tests that context definitions are available on the derived action.
   */
  public function testContextDefinitions() {
    $rule = $this->rulesExpressionManager->createRule();
    $rule
      ->addAction('rules_entity_save', ContextConfig::create()
        ->map('entity', 'entity')
      )
      ->addAction('rules_test_string', ContextConfig::create()
        ->setValue('text', 'x')
      );

    $rules_config = new RulesComponentConfig([
      'id' => 'test_rule',
      'label' => 'Test rule',
    ], 'rules_component');
    $rules_config->setExpression($rule);

    $context_definitions = ['entity' => ContextDefinition::create('entity')];
    $rules_config->setContextDefinitions($context_definitions);
    $provided_definitions = ['concatenated' => ContextDefinition::create('string')];
    $rules_config->setProvidedContextDefinitions($provided_definitions);

    $this->prophesizeStorage([$rules_config]);

    $definition = $this->actionManager->getDefinition('rules_component:test_rule');
    $this->assertEquals($context_definitions, $definition['context']);
    $this->assertEquals($provided_definitions, $definition['provides']);
  }

  /**
   * Tests that a rules component in an action can also provide variables.
   */
  public function testExecutionProvidedVariables() {
    // Create a rule that produces a provided string variable.
    $nested_rule = $this->rulesExpressionManager->createRule();
    $nested_rule->addAction('rules_test_string', ContextConfig::create()
      ->setValue('text', 'x')
    );

    $rules_config = new RulesComponentConfig([
      'id' => 'test_rule',
      'label' => 'Test rule',
    ], 'rules_component');
    $rules_config->setExpression($nested_rule);
    $rules_config->setProvidedContextDefinitions(['concatenated' => ContextDefinition::create('string')]);

    $this->prophesizeStorage([$rules_config]);

    // Invoke the rules component in another rule.
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_component:test_rule');

    $result = RulesComponent::create($rule)
      ->provideContext('concatenated')
      ->execute();

    $this->assertEquals('xx', $result['concatenated']);
  }

  /**
   * Tests that auto saving is only triggered once with nested components.
   */
  public function testAutosaveOnlyOnce() {
    $entity = $this->prophesizeEntity(EntityInterface::class);

    $nested_rule = $this->rulesExpressionManager->createRule();
    $nested_rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'entity')
    );

    $rules_config = new RulesComponentConfig([
      'id' => 'test_rule',
      'label' => 'Test rule',
    ], 'rules_component');
    $rules_config->setExpression($nested_rule);
    $rules_config->setContextDefinitions(['entity' => ContextDefinition::create('entity')]);

    $this->prophesizeStorage([$rules_config]);

    // Create a rule with a nested rule. Overall there are 2 actions to set the
    // entity then.
    $rule = $this->rulesExpressionManager->createRule();
    $rule->addAction('rules_component:test_rule', ContextConfig::create()
      ->map('entity', 'entity')
    );
    $rule->addAction('rules_entity_save', ContextConfig::create()
      ->map('entity', 'entity')
    );

    // Auto-saving should only be triggered once on the entity.
    $entity->save()->shouldBeCalledTimes(1);

    RulesComponent::create($rule)
      ->addContextDefinition('entity', ContextDefinition::create('entity'))
      ->setContextValue('entity', $entity->reveal())
      ->execute();
  }

  /**
   * Prepares a mocked entity storage that returns the provided Rules configs.
   *
   * @param \Drupal\rules\Engine\RulesComponentConfig[] $rules_configs
   *   The Rules componentn config entities that should be returned.
   */
  protected function prophesizeStorage(array $rules_configs) {
    $storage = $this->prophesize(ConfigEntityStorageInterface::class);
    $keyed_configs = [];

    foreach ($rules_configs as $rules_config) {
      $keyed_configs[$rules_config->id()] = $rules_config;
      $storage->load($rules_config->id())->willReturn($rules_config);
    }

    $storage->loadMultiple(NULL)->willReturn($keyed_configs);
    $this->entityTypeManager->getStorage('rules_component')->willReturn($storage->reveal());
  }

}
