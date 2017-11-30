<?php

namespace Drupal\Tests\rules\Kernel;

/**
 * Tests that mails actually go out twith send email action.
 *
 * @group Rules
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class RulesMailTest extends RulesDrupalTestBase {

  /**
   * The action manager used to instantiate the action plugin.
   *
   * @var \Drupal\rules\Core\RulesActionManagerInterface
   */
  protected $actionManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // Use the state system collector mail backend.
    $this->container->get('config.factory')->getEditable('system.mail')
      ->set('interface.default', 'test_mail_collector')
      ->save();

    // Reset the state variable that holds sent messages.
    $this->container->get('state')->set('system.test_mail_collector', []);

    $this->actionManager = $this->container->get('plugin.manager.rules_action');
  }

  /**
   * Checks the From: and Reply-to: headers.
   */
  public function testSubjectAndBody() {
    // Create action to send email.
    $action = $this->actionManager->createInstance('rules_send_email');

    // Add context values to action.
    $action->setContextValue('to', ['mail@example.com'])
      ->setContextValue('subject', 'subject')
      ->setContextValue('message', 'hello');

    // Send email.
    $action->execute();

    // Retrieve sent message.
    $captured_emails = $this->container->get('state')->get('system.test_mail_collector');
    $sent_message = end($captured_emails);

    // Check to make sure that our subject and body are as expected.
    $this->assertEquals($sent_message['to'], 'mail@example.com');
    $this->assertEquals($sent_message['subject'], 'subject');
    // Need to trim the email body to get rid of newline at end.
    $this->assertEquals(trim($sent_message['body']), 'hello');
  }

}
