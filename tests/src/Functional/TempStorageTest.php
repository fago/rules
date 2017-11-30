<?php

namespace Drupal\Tests\rules\Functional;

/**
 * Tests that editing a rule locks it for another user.
 *
 * @group RulesUi
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class TempStorageTest extends RulesBrowserTestBase {

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
   * Tests that editing a rule locks it for another user.
   */
  public function testLocking() {
    // Create a rule with the first user.
    $account_1 = $this->drupalCreateUser(['administer rules']);
    $this->drupalLogin($account_1);

    $this->drupalGet('admin/config/workflow/rules');
    $this->clickLink('Add reaction rule');

    $this->fillField('Label', 'Test rule');
    $this->fillField('Machine-readable name', 'test_rule');
    $this->fillField('React on event', 'rules_entity_insert:node');
    $this->pressButton('Save');

    $this->clickLink('Add condition');
    $this->fillField('Condition', 'rules_node_is_promoted');
    $this->pressButton('Continue');

    $this->fillField('context[node][setting]', '1');
    $this->pressButton('Save');

    $this->assertSession()->pageTextContains('You have unsaved changes.');

    // Now check with the second user that the rule is being edited and locked.
    $account_2 = $this->drupalCreateUser(['administer rules']);
    $this->drupalLogin($account_2);

    $this->drupalGet('admin/config/workflow/rules/reactions/edit/test_rule');
    $this->assertSession()->pageTextContains('This rule is being edited by user ' . $account_1->getUsername() . ', and is therefore locked from editing by others.');

    $this->pressButton('Cancel');
    $this->assertSession()->pageTextNotContains('Canceled.');
    $this->assertSession()->pageTextContains('This rule is being edited by user ' . $account_1->getUsername() . ', and is therefore locked from editing by others.');

    $this->pressButton('Save');
    $this->assertSession()->pageTextNotContains('Reaction rule Test rule has been updated.');
    $this->assertSession()->pageTextContains('This rule is being edited by user ' . $account_1->getUsername() . ', and is therefore locked from editing by others.');

    $this->clickLink('Edit');
    $current_url = $this->getSession()->getCurrentUrl();
    $this->pressButton('Save');

    $this->assertEquals($current_url, $this->getSession()->getCurrentUrl());
    $this->assertSession()->pageTextContains('This rule is being edited by user ' . $account_1->getUsername() . ', and is therefore locked from editing by others.');

    // Try breaking the lock to edit the rule.
    $this->clickLink('break this lock');

    $this->assertSession()->pageTextContains('By breaking this lock, any unsaved changes made by ' . $account_1->getUsername() . ' will be lost.');
    $this->pressButton('Break lock');

    $this->assertSession()->pageTextContains('The lock has been broken and you may now edit this rule.');
    // The link to edit the condition is now gone because the changes have been
    // reverted.
    $this->assertFalse($this->getSession()->getPage()->hasLink('Edit'));
  }

}
