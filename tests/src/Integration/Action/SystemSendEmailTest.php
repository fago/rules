<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\SystemSendEmailTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;
use Drupal\Core\Language\LanguageInterface;
use Psr\Log\LogLevel;
use Drupal\Component\Utility\SafeMarkup;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\SystemSendEmail
 * @group rules_actions
 */
class SystemSendEmailTest extends RulesIntegrationTestBase {

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Plugin\Action\SystemSendEmail
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->logger = $this->getMock('Psr\Log\LoggerInterface');

    $this->mailManager = $this->getMockBuilder('Drupal\Core\Mail\MailManagerInterface')
      ->getMock();

    $this->container->set('logger.factory', $this->logger);
    $this->container->set('plugin.manager.mail', $this->mailManager);

    $this->action = $this->actionManager->createInstance('rules_send_email');
  }


  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Send email', $this->action->summary());
  }

  /**
   * Tests sending a mail to one recipient.
   *
   * @covers ::execute
   */
  public function testSendMailToOneRecipient() {
    $to = ['mail@example.com'];
    $this->action->setContextValue('to', $to)
      ->setContextValue('subject', 'subject')
      ->setContextValue('message', 'hello');

    $language = $this->action->getContextValue('language');
    $langcode = isset($language) ? $language->getId() : LanguageInterface::LANGCODE_SITE_DEFAULT;
    $params = [
      'subject' => $this->action->getContextValue('subject'),
      'message' => $this->action->getContextValue('message'),
    ];

    $this->mailManager
      ->expects($this->once())
      ->method('mail')
      ->with('rules', 'rules_action_mail_' . $this->action->getPluginId(), implode(', ', $to), $langcode, $params)
      ->willReturn(['result' => TRUE]);

    $this->logger
      ->expects($this->once())
      ->method('log')
      ->with(LogLevel::NOTICE, SafeMarkup::format('Successfully sent email to %to', ['%to' => implode(', ', $to)]));

    $this->action->execute();
  }

  /**
   * Tests sending a mail to two recipients.
   *
   * @covers ::execute
   */
  public function testSendMailToTwoRecipients() {
    $to = ['mail@example.com', 'mail2@example.com'];
    $this->action->setContextValue('to', $to)
      ->setContextValue('subject', 'subject')
      ->setContextValue('message', 'hello');

    $language = $this->action->getContextValue('language');
    $langcode = isset($language) ? $language->getId() : LanguageInterface::LANGCODE_SITE_DEFAULT;
    $params = [
      'subject' => $this->action->getContextValue('subject'),
      'message' => $this->action->getContextValue('message'),
    ];

    $this->mailManager
      ->expects($this->once())
      ->method('mail')
      ->with('rules', 'rules_action_mail_' . $this->action->getPluginId(), implode(', ', $to), $langcode, $params)
      ->willReturn(['result' => TRUE]);

    $this->logger
      ->expects($this->once())
      ->method('log')
      ->with(LogLevel::NOTICE, SafeMarkup::format('Successfully sent email to %to', ['%to' => implode(', ', $to)]));

    $this->action->execute();
  }

}
