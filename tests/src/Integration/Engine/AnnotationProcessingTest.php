<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Engine\AnnotationProcessingTest.
 */

namespace Drupal\Tests\rules\Integration\Engine;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;
use Drupal\Core\Session\SessionManagerInterface;

/**
 * Tests processing of the ContextDefinition annotation.
 *
 * @group rules
 */
class AnnotationProcessingTest extends RulesIntegrationTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->enableModule('user');
    $session_manager = $this->prophesize(SessionManagerInterface::class);
    $this->container->set('session_manager', $session_manager->reveal());
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
   *
   * @group rules
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
