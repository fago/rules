<?php

namespace Drupal\Tests\rules\Kernel;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests rules redirect action event subscriber.
 *
 * @coversDefaultClass \Drupal\rules\EventSubscriber\RedirectEventSubscriber
 *
 * @group RulesEvent
 * @group legacy
 * @todo Remove the 'legacy' tag when Rules no longer uses deprecated code.
 * @see https://www.drupal.org/project/rules/issues/2922757
 */
class RedirectEventSubscriberTest extends RulesDrupalTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Drupal 8.0.x needs the router table installed which is done automatically
    // in Drupal 8.1.x. Remove this once Drupal 8.0.x is unsupported.
    if (!empty(drupal_get_module_schema('system', 'router'))) {
      $this->installSchema('system', ['router']);
      $this->container->get('router.builder')->rebuild();
    }
  }

  /**
   * Test the response is a redirect if a redirect url is added to the request.
   *
   * @covers ::checkRedirectIssued
   */
  public function testCheckRedirectIssued() {
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel */
    $http_kernel = $this->container->get('http_kernel');

    $request = Request::create('/');
    $request->attributes->set('_rules_redirect_action_url', '/test/redirect/url');

    $response = $http_kernel->handle($request);

    $this->assertInstanceOf(RedirectResponse::class, $response, "The response is a redirect.");
    $this->assertEquals('/test/redirect/url', $response->getTargetUrl(), "The redirect target is the provided url.");
  }

}
