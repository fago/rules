<?php

namespace Drupal\Tests\rules\Functional;

/**
 * Tests that a rule can be configured and triggered when a node is edited.
 *
 * @group RulesUi
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class ConfigureAndExecuteTest extends RulesBrowserTestBase {

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
    $this->clickLink('Add reaction rule');

    $this->fillField('Label', 'Test rule');
    $this->fillField('Machine-readable name', 'test_rule');
    $this->fillField('React on event', 'rules_entity_presave:node');
    $this->pressButton('Save');

    $this->clickLink('Add condition');

    $this->fillField('Condition', 'rules_data_comparison');
    $this->pressButton('Continue');

    // @todo this should not be necessary once the data context is set to
    // selector by default anyway.
    $this->pressButton('Switch to data selection');
    $this->fillField('context[data][setting]', 'node.title.0.value');

    $this->fillField('context[value][setting]', 'Test title');
    $this->pressButton('Save');

    $this->clickLink('Add action');
    $this->fillField('Action', 'rules_system_message');
    $this->pressButton('Continue');

    $this->fillField('context[message][setting]', 'Title matched "Test title"!');
    $this->fillField('context[type][setting]', 'status');
    $this->pressButton('Save');

    // One more save to permanently store the rule.
    $this->pressButton('Save');

    // Add a node now and check if our rule triggers.
    $this->drupalGet('node/add/article');
    $this->fillField('Title', 'Test title');
    $this->pressButton('Save');

    $this->assertSession()->pageTextContains('Title matched "Test title"!');

    // Edit the rule and negate the condition.
    $this->drupalGet('admin/config/workflow/rules/reactions/edit/test_rule');
    $this->clickLink('Edit', 0);
    $this->getSession()->getPage()->checkField('negate');
    $this->pressButton('Save');
    // One more save to permanently store the rule.
    $this->pressButton('Save');

    // Need to clear cache so that the edited version will be used.
    drupal_flush_all_caches();
    // Create node with same title and check that the message is not shown.
    $this->drupalGet('node/add/article');
    $this->fillField('Title', 'Test title');
    $this->pressButton('Save');
    $this->assertSession()->pageTextNotContains('Title matched "Test title"!');
  }

}
