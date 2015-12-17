<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Functional\UiPageTest.
 */

namespace Drupal\Tests\rules\Functional;

use Drupal\simpletest\BrowserTestBase;

/**
 * Tests that the Rules UI pages are reachable.
 *
 * @group rules_ui
 */
class UiPageTest extends BrowserTestBase {

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
    $this->getSession()->getPage()->findLink('Add reaction rule')->click();

    $this->getSession()->getPage()->findField('Label')->setValue('Test rule');
    $this->getSession()->getPage()->findField('Machine-readable name')->setValue('test_rule');
    $this->getSession()->getPage()->findButton('Save')->click();

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Reaction rule Test rule has been created.');

    $this->getSession()->getPage()->findLink('Add condition')->click();

    $this->getSession()->getPage()->findField('Condition')->setValue('rules_node_is_promoted');
    $this->getSession()->getPage()->findButton('Continue')->click();

    $this->getSession()->getPage()->findField('Node')->setValue('1');
    $this->getSession()->getPage()->findButton('Save')->click();

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Your changes have been saved.');
  }

  /**
   * Tests that deleting an expression from a rule works.
   */
  public function testDeleteExpressionInRule() {
    // Setup a rule with one condition.
    $this->testCreateReactionRule();

    $this->getSession()->getPage()->findLink('Delete')->click();
    $this->assertSession()->pageTextContains('Are you sure you want to delete Condition: Node is promoted from Test rule?');

    $this->getSession()->getPage()->findButton('Delete')->click();
    $this->assertSession()->pageTextContains('Your changes have been saved.');
  }

}
