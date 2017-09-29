<?php

namespace Drupal\Tests\rules\Unit\Integration\Engine;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * Tests processing of the ContextDefinition annotation.
 *
 * @group Rules
 */
class AnnotationProcessingTest extends RulesIntegrationTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->enableModule('user');
    // Some of our plugins assume sessions exist:
    $session_manager = $this->prophesize(SessionManagerInterface::class);
    $this->container->set('session_manager', $session_manager->reveal());
  }

  /**
   * Make sure @ Translation annotations do not leak out into the wild.
   */
  public function testTranslationSquelching() {
    // Get a sample Rules plugin.
    $plugin = $this->conditionManager->createInstance('rules_list_contains');
    $context = $plugin->getContext('list');
    $definition = $context->getContextDefinition();

    // These can reasonable be string or TranslatableMarkup, but never
    // a Translation object.
    $label = $definition->getLabel();
    $description = $definition->getDescription();
    $this->assertFalse($label instanceof Translation, 'Label is not a Translation object');
    $this->assertFalse($description instanceof Translation, 'Description is not a Translation object');

    // Check also the toArray() path.
    $definition = $context->getContextDefinition();
    $values = $definition->toArray();
    $label = $values['label'];
    $description = $values['description'];
    $this->assertFalse($label instanceof Translation, "\$values['label'] is not a Translation object");
    $this->assertFalse($description instanceof Translation, "\$values['description'] is not a Translation object");
  }

  /**
   * Tests if our ContextDefinition annotations are correctly processed.
   *
   * @param string $plugin_type
   *   Type of rules plugin to test (for now, 'action' or 'condition').
   * @param string $plugin_id
   *   Plugin ID for the plugin to be tested.
   * @param string $context_name
   *   The name of the plugin's context to test.
   * @param string $expected
   *   The type of context as defined in the plugin's annotation.
   *
   * @dataProvider provideRulesPlugins
   */
  public function testCheckConfiguration($plugin_type, $plugin_id, $context_name, $expected) {
    $plugin = NULL;

    switch ($plugin_type) {
      case 'action':
        $plugin = $this->actionManager->createInstance($plugin_id);
        break;

      case 'condition':
        $plugin = $this->conditionManager->createInstance($plugin_id);
        break;
    }

    $this->assertNotNull($plugin, "{$plugin_type} plugin {$plugin_id} loads");

    $context = $plugin->getContext($context_name);

    $this->assertNotNull($context, "Plugin {$plugin_id} has context {$context_name}");

    $context_def = $context->getContextDefinition();
    $type = $context_def->getDataType();

    $this->assertSame($type, $expected, "Context type for {$context_name} is $expected");
  }

  /**
   * Provider for plugins to test.
   *
   * Passes $plugin_type, $plugin_id, $context_name, and $expected.
   *
   * @return array
   *   Array of array of values to be passed to our test.
   */
  public function provideRulesPlugins() {
    return [
      [
        'action',
        'rules_user_block',
        'user',
        'entity:user',
      ],
      [
        'condition',
        'rules_entity_is_of_bundle',
        'entity',
        'entity',
      ],
      [
        'condition',
        'rules_node_is_promoted',
        'node',
        'entity:node',
      ],
      [
        'action',
        'rules_list_item_add',
        'list',
        'list',
      ],
      [
        'action',
        'rules_list_item_add',
        'item',
        'any',
      ],
      [
        'action',
        'rules_list_item_add',
        'unique',
        'boolean',
      ],
      [
        'action',
        'rules_list_item_add',
        'pos',
        'string',
      ],
    ];
  }

}
