<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\BanIPTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\BanIP
 * @group rules_action
 */
class BanIPTest extends RulesIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\ban\BanIpManagerInterface
   */
  protected $banManager;

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // We need the ban module.
    $this->enableModule('ban');
    $this->banManager = $this->getMock('Drupal\ban\BanIpManagerInterface');
    $this->container->set('ban.ip_manager', $this->banManager);

    $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
    $this->container->set('request', $this->request);

    $this->action = $this->actionManager->createInstance('rules_ban_ip');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Ban an IP address', $this->action->summary());
  }

  /**
   * Tests the action execution with Context IPv4.
   *
   * See http://en.wikipedia.org/wiki/Reserved_IP_addresses
   * See https://tools.ietf.org/html/rfc3330 : "192.0.2.0/24 - This block is
   * assigned as "TEST-NET" for use in documentation and example code. It is
   * often used in conjunction with domain names example.com or example.net in
   * vendor and protocol documentation. Addresses within this block should not
   * appear on the public Internet."
   *
   * @covers ::execute
   */
  public function testActionExecutionWithContextIPv4() {
    // TEST-NET-1 IPv4.
    $IPv4 = '192.0.2.0';
    $this->action->setContextValue('ip', $IPv4);

    $this->banManager
      ->expects($this->once())
      ->method('banIp')
      ->with($IPv4);

    $this->action->execute();

  }

  /**
   * Tests the action execution with Context IPv6.
   *
   * See http://en.wikipedia.org/wiki/Reserved_IP_addresses
   * See https://tools.ietf.org/html/rfc3330 : "192.0.2.0/24 - This block is
   * assigned as "TEST-NET" for use in documentation and example code. It is
   * often used in conjunction with domain names example.com or example.net in
   * vendor and protocol documentation. Addresses within this block should not
   * appear on the public Internet."
   *
   * @covers ::execute
   */
  public function testActionExecutionWithContextIPv6() {
    // TEST-NET-1 IPv4 '192.0.2.0' converted to IPv6.
    $IPv6 = '2002:0:0:0:0:0:c000:200';
    $this->action->setContextValue('ip', $IPv6);

    $this->banManager
      ->expects($this->once())
      ->method('banIp')
      ->with($IPv6);

    $this->action->execute();

  }

  /**
   * Tests the action execution without Context IP set.
   *
   * Should fallback to the current IP of the request.
   *
   * @covers ::execute
   */
  public function testActionExecutionWithoutContextIP() {
    // TEST-NET-1 IPv4.
    $ip = '192.0.2.0';

    $this->request
      ->expects($this->once())
      ->method('getClientIP')
      ->willReturn($ip);

    $this->banManager
      ->expects($this->once())
      ->method('banIp')
      ->with($ip);

    $this->action->execute();

  }

}
