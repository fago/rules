<?php

namespace Drupal\Tests\rules\Unit\Integration\Action;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasStorageInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\RulesAction\PathAliasCreate
 * @group RulesAction
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
   * @var \Drupal\Core\Path\AliasStorageInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $aliasStorage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->aliasStorage = $this->prophesize(AliasStorageInterface::class);
    $this->container->set('path.alias_storage', $this->aliasStorage->reveal());

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
    $this->aliasStorage->save('node/1', 'about', LanguageInterface::LANGCODE_NOT_SPECIFIED)
      ->shouldBeCalledTimes(1);

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
    $language = $this->prophesize(LanguageInterface::class);
    $language->getId()->willReturn('en');

    $this->aliasStorage->save('node/1', 'about', 'en')
      ->shouldBeCalledTimes(1);

    $this->action->setContextValue('source', 'node/1')
      ->setContextValue('alias', 'about')
      ->setContextValue('language', $language->reveal());

    $this->action->execute();
  }

}
