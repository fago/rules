<?php

namespace Drupal\Tests\rules\Kernel;

/**
 * Tests that rules_entity_view() does not throw fatal errors.
 *
 * @group Rules
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class EntityViewTest extends RulesDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['field', 'node', 'text', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installConfig(['system']);
    $this->installConfig(['field']);
    $this->installConfig(['node']);
    $this->installSchema('system', ['sequences']);

    // Drupal 8.0.x needs the router table installed which is done automatically
    // in Drupal 8.1.x. Remove this once Drupal 8.0.x is unsupported.
    if (!empty(drupal_get_module_schema('system', 'router'))) {
      $this->installSchema('system', ['router']);
    }

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    // Make sure that the node routes get picked when used during rendering.
    $this->container->get('router.builder')->rebuild();
  }

  /**
   * Tests that rules_entity_view() can be invoked correctly.
   */
  public function testEntityViewHook() {
    // Create a node.
    $entity_type_manager = $this->container->get('entity_type.manager');
    $entity_type_manager->getStorage('node_type')
      ->create([
        'type' => 'page',
        'display_submitted' => FALSE,
      ])
      ->save();

    $node = $entity_type_manager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
      ]);
    $node->save();

    // Build the node render array and render it, so that hook_entity_view() is
    // invoked.
    $view_builder = $entity_type_manager->getViewBuilder('node');
    $build = $view_builder->view($node);
    $this->container->get('renderer')->renderPlain($build);
  }

}
