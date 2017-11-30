<?php

namespace Drupal\Tests\rules\Functional;

/**
 * Tests that the Rules UI pages are reachable.
 *
 * @group RulesUi
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class UiPageTest extends RulesBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules'];

  /**
   * We use the minimal profile because we want to test local action links.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * Tests that the reaction rule listing page works.
   */
  public function testReactionRulePage() {
    $account = $this->drupalCreateUser(['administer rules']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/workflow/rules');
    $this->assertSession()->statusCodeEquals(200);

    // Test that there is an empty reaction rule listing.
    $this->assertSession()->pageTextContains('There is no Reaction Rule yet.');
  }

  /**
   * Tests that creating a reaction rule works.
   */
  public function testCreateReactionRule() {
    $account = $this->drupalCreateUser(['administer rules']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/workflow/rules');
    $this->clickLink('Add reaction rule');

    $this->fillField('Label', 'Test rule');
    $this->fillField('Machine-readable name', 'test_rule');
    $this->fillField('React on event', 'rules_entity_insert:node');

    $this->pressButton('Save');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Reaction rule Test rule has been created.');

    $this->clickLink('Add condition');

    $this->fillField('Condition', 'rules_node_is_promoted');
    $this->pressButton('Continue');

    $this->fillField('context[node][setting]', '1');
    $this->pressButton('Save');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('You have unsaved changes.');

    $this->pressButton('Save');
    $this->assertSession()->pageTextContains('Reaction rule Test rule has been updated. ');
  }

  /**
   * Tests that cancelling an expression from a rule works.
   */
  public function testCancelExpressionInRule() {
    // Setup a rule with one condition.
    $this->testCreateReactionRule();

    $this->clickLink('Add condition');
    $this->fillField('Condition', 'rules_node_is_promoted');
    $this->pressButton('Continue');

    $this->fillField('context[node][setting]', '1');
    $this->pressButton('Save');

    $this->assertSession()->pageTextContains('You have unsaved changes.');

    // Edit and cancel.
    $this->pressButton('Cancel');
    $this->assertSession()->pageTextContains('Canceled.');

    // Make sure that we are back at the overview listing page.
    $this->assertEquals(1, preg_match('#/admin/config/workflow/rules$#', $this->getSession()->getCurrentUrl()));
  }

  /**
   * Tests that deleting an expression from a rule works.
   */
  public function testDeleteExpressionInRule() {
    // Setup a rule with one condition.
    $this->testCreateReactionRule();

    $this->clickLink('Delete');
    $this->assertSession()->pageTextContains('Are you sure you want to delete Condition: Node is promoted from Test rule?');

    $this->pressButton('Delete');
    $this->assertSession()->pageTextContains('You have unsaved changes.');

    $this->pressButton('Save');
    $this->assertSession()->pageTextContains('Reaction rule Test rule has been updated. ');
  }

  /**
   * Tests that an action with a multiple context can be confugured.
   */
  public function testMultipleContextAction() {
    $account = $this->drupalCreateUser(['administer rules']);
    $this->drupalLogin($account);

    $this->drupalGet('admin/config/workflow/rules');
    $this->clickLink('Add reaction rule');

    $this->fillField('Label', 'Test rule');
    $this->fillField('Machine-readable name', 'test_rule');
    $this->fillField('React on event', 'rules_entity_insert:node');

    $this->pressButton('Save');

    $this->clickLink('Add action');
    $this->fillField('Action', 'rules_send_email');
    $this->pressButton('Continue');

    // Push the data selection switch 2 times to make sure that also works and
    // does not throw PHP notices.
    $this->pressButton('Switch to data selection');
    $this->pressButton('Switch to data selection');

    $this->fillField('context[to][setting]', 'klausi@example.com');
    $this->fillField('context[subject][setting]', 'subject');
    $this->fillField('context[message][setting]', 'message');
    $this->pressButton('Save');

    $this->assertSession()->statusCodeEquals(200);
  }

}
