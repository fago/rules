<?php

namespace Drupal\Tests\rules\Kernel;

use Drupal\rules\Ui\RulesUiConfigHandler;
use Drupal\rules\Ui\RulesUiDefinition;

/**
 * Tests embedding the Rules UI.
 *
 * @group RulesUi
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
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
   * @var \Drupal\rules\Ui\RulesUiManagerInterface
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
    $this->installSchema('system', ['sequences']);
  }

  /**
   * @cover \Drupal\rules\Ui\RulesUiManager
   */
  public function testUiManager() {
    $definition = $this->rulesUiManager->getDefinitions();
    $this->assertTrue(isset($definition['rules_test_ui_embed.settings_conditions']));
    $this->assertInstanceOf(RulesUiDefinition::class, $definition['rules_test_ui_embed.settings_conditions']);
    $this->assertTrue(!empty($definition['rules_test_ui_embed.settings_conditions']->label));
    $this->assertEquals(RulesUiConfigHandler::class, $definition['rules_test_ui_embed.settings_conditions']->getClass());
  }

}
