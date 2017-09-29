<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\PathAliasExists
 * @group RulesCondition
 */
class PathAliasExistsTest extends RulesIntegrationTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Core\RulesConditionInterface
   */
  protected $condition;

  /**
   * A mocked language object (english).
   *
   * @var \Drupal\Core\Language\LanguageInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $englishLanguage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->condition = $this->conditionManager->createInstance('rules_path_alias_exists');

    $this->englishLanguage = $this->prophesize(LanguageInterface::class);
    $this->englishLanguage->getId()->willReturn('en');
  }

  /**
   * Tests that the dependencies are properly set in the constructor.
   *
   * @covers ::__construct
   */
  public function testConstructor() {
    $property = new \ReflectionProperty($this->condition, 'aliasManager');
    $property->setAccessible(TRUE);

    $this->assertSame($this->aliasManager->reveal(), $property->getValue($this->condition));
  }

  /**
   * Tests evaluating the condition for an alias that can be resolved.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationAliasWithPath() {
    $this->aliasManager->getPathByAlias('alias-for-path', NULL)
      ->willReturn('path-with-alias')
      ->shouldBeCalledTimes(1);

    $this->aliasManager->getPathByAlias('alias-for-path', 'en')
      ->willReturn('path-with-alias')
      ->shouldBeCalledTimes(1);

    // First, only set the path context.
    $this->condition->setContextValue('alias', 'alias-for-path');

    // Test without language context set.
    $this->assertTrue($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->englishLanguage->reveal());
    $this->assertTrue($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition for an alias that can not be resolved.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationAliasWithoutPath() {
    $this->aliasManager->getPathByAlias('alias-for-path-that-does-not-exist', NULL)
      ->willReturn('alias-for-path-that-does-not-exist')
      ->shouldBeCalledTimes(1);

    $this->aliasManager->getPathByAlias('alias-for-path-that-does-not-exist', 'en')
      ->willReturn('alias-for-path-that-does-not-exist')
      ->shouldBeCalledTimes(1);

    // First, only set the path context.
    $this->condition->setContextValue('alias', 'alias-for-path-that-does-not-exist');

    // Test without language context set.
    $this->assertFalse($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->englishLanguage->reveal());
    $this->assertFalse($this->condition->evaluate());
  }

}
