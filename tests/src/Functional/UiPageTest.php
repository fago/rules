<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Functional\UiPageTest.
 */

namespace Drupal\Tests\rules\Functional;

use Drupal\simpletest\BrowserTestBase;

/**
 * Tests that the Rules UI pages a reachable.
 *
 * @group rules_ui
 *
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 */
class UiPageTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules'];

  /**
   * Tests that the reaction rule listing page works.
   */
  public function testReactionRulePage() {
    $account = $this->drupalCreateUser(['administer rules']);
    $this->drupalLogin($account);

    // Visit a Drupal page that requires login.
    $this->drupalGet('admin/config/workflow/rules');
    $this->assertSession()->statusCodeEquals(200);

    // Test that there is an empty reaction rule listing.
    $this->assertSession()->pageTextContains('There is no Reaction Rule yet.');
  }

}
