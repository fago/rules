<?php

/**
 * @file
 * Contains Drupal\Tests\rules\Kernel\RulesUiEmbedTest.
 */

namespace Drupal\Tests\rules\Kernel;

use Drupal\rules\Core\RulesUiDefaultHandler;

/**
 * Tests embedding the Rules UI.
 */
class RulesUiEmbedTest extends RulesDrupalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules', 'rules_test_ui_embed', 'system', 'user'];

  /**
   * The rules UI manager.
   *
   * @var \Drupal\rules\Core\RulesUiManagerInterface
   */
  protected $rulesUiManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->rulesUiManager = $this->container->get('plugin.manager.rules_ui');

    $this->installConfig(['system']);
    $this->installConfig(['rules_test_ui_embed']);
    $this->installSchema('system', ['router', 'sequences']);

    // Make sure that the module routes get picked when used during rendering.
    $this->container->get('router.builder')->rebuild();
  }

  /**
   * @cover \Drupal\rules\Core\RulesUiManager
   */
  public function testUiManager() {
    $definition = $this->rulesUiManager->getDefinitions();
    $this->assertTrue(isset($definition['rules_test_ui_embed.settings_conditions']));
    $this->assertTrue(isset($definition['rules_test_ui_embed.settings_conditions']['label']));
    $this->assertEquals(RulesUiDefaultHandler::class, $definition['rules_test_ui_embed.settings_conditions']['class']);
  }

}
