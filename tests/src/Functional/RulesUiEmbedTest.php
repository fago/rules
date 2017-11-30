<?php

namespace Drupal\Tests\rules\Functional;

/**
 * Functional test for the embedded Rules example implementation.
 *
 * @group RulesUi
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class RulesUiEmbedTest extends RulesBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules_test_ui_embed'];

  /**
   * @cover \Drupal\rules_test_ui_embed\Form\SettingsForm
   */
  public function testExampleUi() {
    $account = $this->drupalCreateUser([
      'administer rules',
      'access administration pages',
    ]);
    $this->drupalLogin($account);
    $this->drupalGet('admin/config/user-interface');

    $this->drupalGet('admin/config/user-interface/css');
    $this->clickLink('Add condition');
    $this->fillField('Condition', 'rules_data_comparison');
    $this->pressButton('Continue');
    $this->fillField('context[data][setting]', '123');
    $this->fillField('context[value][setting]', '234');
    $this->pressButton('Save');

    // Now the condition should be listed. Try editing it.
    $this->assertSession()->pageTextContains('Data comparison');
    $this->clickLink('Edit');
    $this->assertSession()->fieldValueEquals('context[data][setting]', '123');
    $this->assertSession()->fieldValueEquals('context[value][setting]', '234');
    $this->fillField('context[value][setting]', '123');
    $this->pressButton('Save');
    $this->assertSession()->pageTextContains('Data comparison');
    // One more save to permanently store the changes.
    $this->fillField('css_file', 'css/test2.css');
    $this->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');

    // Reload and ensure data is still there.
    $this->drupalGet('admin/config/user-interface/css');
    $this->assertSession()->fieldValueEquals('css_file', 'css/test2.css');
    $this->assertSession()->pageTextContains('Data comparison');

    // Delete condition and save.
    $this->clickLink('Delete');
    $this->pressButton('Delete');
    $this->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->assertSession()->pageTextNotContains('Data comparison');
  }

}
