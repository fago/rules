<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\PathAliasCreateTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\PathAliasCreate
 * @group rules_actions
 */
class PathAliasCreateTest extends RulesIntegrationTestBase {

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Core\RulesActionInterface
   */
  protected $action;

  /**
   * The mocked alias storage service.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Path\AliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->aliasStorage = $this->getMock('Drupal\Core\Path\AliasStorageInterface');
    $this->container->set('path.alias_storage', $this->aliasStorage);

    $this->action = $this->actionManager->createInstance('rules_path_alias_create');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Create any path alias', $this->action->summary());
  }

  /**
   * Tests the action execution when no language is specified.
   *
   * @covers ::execute
   */
  public function testActionExecutionWithoutLanguage() {
    $this->aliasStorage->expects($this->once())
      ->method('save')
      ->with('node/1', 'about', LanguageInterface::LANGCODE_NOT_SPECIFIED);

    $this->action->setContextValue('source', 'node/1')
      ->setContextValue('alias', 'about');

    $this->action->execute();
  }

  /**
   * Tests the action execution when a language is specified.
   *
   * @covers ::execute
   */
  public function testActionExecutionWithLanguage() {
    $language = $this->getMock('Drupal\Core\Language\LanguageInterface');
    $language->expects($this->any())
      ->method('getId')
      ->will($this->returnValue('en'));

    $this->aliasStorage->expects($this->once())
      ->method('save')
      ->with('node/1', 'about', 'en');

    $this->action->setContextValue('source', 'node/1')
      ->setContextValue('alias', 'about')
      ->setContextValue('language', $language);

    $this->action->execute();
  }

}
