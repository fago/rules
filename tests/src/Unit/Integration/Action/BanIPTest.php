<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\ban\BanIpManagerInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\BanIP
 * @group RulesAction
 */
class BanIPTest extends RulesIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * @var \Drupal\ban\BanIpManagerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $banManager;

  /**
   * @var \Symfony\Component\HttpFoundation\Request|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $request;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    // We need the ban module.
    $this->enableModule('ban');
    $this->banManager = $this->prophesize(BanIpManagerInterface::class);
    $this->container->set('ban.ip_manager', $this->banManager->reveal());

    // Mock a request.
    $this->request = $this->prophesize(Request::class);

    // Mock the request_stack service, make it return our mocked request,
    // and register it in the container.
    $this->requestStack = $this->prophesize(RequestStack::class);
    $this->requestStack->getCurrentRequest()->willReturn($this->request->reveal());
    $this->container->set('request_stack', $this->requestStack->reveal());

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
  public function testActionExecutionWithContextIpv4() {
    // TEST-NET-1 IPv4.
    $ipv4 = '192.0.2.0';
    $this->action->setContextValue('ip', $ipv4);

    $this->banManager->banIp($ipv4)->shouldBeCalledTimes(1);

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
  public function testActionExecutionWithContextIpv6() {
    // TEST-NET-1 IPv4 '192.0.2.0' converted to IPv6.
    $ipv6 = '2002:0:0:0:0:0:c000:200';
    $this->action->setContextValue('ip', $ipv6);

    $this->banManager->banIp($ipv6)->shouldBeCalledTimes(1);

    $this->action->execute();

  }

  /**
   * Tests the action execution without Context IP set.
   *
   * Should fallback to the current IP of the request.
   *
   * @covers ::execute
   */
  public function testActionExecutionWithoutContextIp() {
    // TEST-NET-1 IPv4.
    $ip = '192.0.2.0';

    $this->request->getClientIp()->willReturn($ip)->shouldBeCalledTimes(1);

    $this->banManager->banIp($ip)->shouldBeCalledTimes(1);

    $this->action->execute();
  }

}
