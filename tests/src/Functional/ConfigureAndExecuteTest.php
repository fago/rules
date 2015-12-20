<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Functional\ConfigureAndExecuteTest.
 */

namespace Drupal\Tests\rules\Functional;

use Drupal\simpletest\BrowserTestBase;

/**
 * Tests that a rule can be configured and triggered when a node is edited.
 *
 * @group rules_ui
 */
class ConfigureAndExecuteTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['node', 'rules'];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create an article content type that we will use for testing.
    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => 'article',
        'name' => 'Article',
      ]);
    $type->save();
    $this->container->get('router.builder')->rebuild();

  }

  /**
   * Tests creation of a rule and then triggering its execution.
   */
  public function testConfigureAndExecute() {
    $account = $this->drupalCreateUser([
      'create article content',
      'administer rules',
      'administer site configuration',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/workflow/rules');

    // Set up a rule that will show a system message if the title of a node
    // matches "Test title".
    $this->getSession()->getPage()->findLink('Add reaction rule')->click();

    $this->getSession()->getPage()->findField('Label')->setValue('Test rule');
    $this->getSession()->getPage()->findField('Machine-readable name')->setValue('test_rule');
    $this->getSession()->getPage()->findField('React on event')->setValue('rules_entity_presave:node');
    $this->getSession()->getPage()->findButton('Save')->click();

    $this->getSession()->getPage()->findLink('Add condition')->click();

    $this->getSession()->getPage()->findField('Condition')->setValue('rules_data_comparison');
    $this->getSession()->getPage()->findButton('Continue')->click();

    // @todo this should not be necessary once the data context is set to
    // selector by default anyway.
    $this->getSession()->getPage()->findButton('Switch to data selection')->click();
    $this->getSession()->getPage()->findField('context[data][setting]')->setValue('node:title:0:value');

    $this->getSession()->getPage()->findField('context[value][setting]')->setValue('Test title');
    $this->getSession()->getPage()->findButton('Save')->click();

    $this->getSession()->getPage()->findLink('Add action')->click();
    $this->getSession()->getPage()->findField('Action')->setValue('rules_system_message');
    $this->getSession()->getPage()->findButton('Continue')->click();

    $this->getSession()->getPage()->findField('context[message]')->setValue('Title matched "Test title"!');
    $this->getSession()->getPage()->findField('context[type]')->setValue('status');
    $this->getSession()->getPage()->findButton('Save')->click();

    // Rebuild the container so that the new Rules event is picked up.
    $this->drupalGet('admin/config/development/performance');
    $this->getSession()->getPage()->findButton('Clear all caches')->click();

    // Add a node now and check if our rule triggers.
    $this->drupalGet('node/add/article');
    $this->getSession()->getPage()->findField('Title')->setValue('Test title');
    $this->getSession()->getPage()->findButton('Save')->click();

    $this->assertSession()->pageTextContains('Title matched "Test title"!');
  }

}
