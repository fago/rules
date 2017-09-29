<?php

namespace Drupal\Tests\rules\Unit\Integration\Condition;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Tests\rules\Unit\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\PathHasAlias
 * @group RulesCondition
 */
class PathHasAliasTest extends RulesIntegrationTestBase {

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

    $this->condition = $this->conditionManager->createInstance('rules_path_has_alias');

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
   * Tests evaluating the condition for a path with an alias.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationPathWithAlias() {
    $this->aliasManager->getAliasByPath('path-with-alias', NULL)
      ->willReturn('alias-for-path')
      ->shouldBeCalledTimes(1);

    $this->aliasManager->getAliasByPath('path-with-alias', 'en')
      ->willReturn('alias-for-path')
      ->shouldBeCalledTimes(1);

    // First, only set the path context.
    $this->condition->setContextValue('path', 'path-with-alias');

    // Test without language context set.
    $this->assertTrue($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->englishLanguage->reveal());
    $this->assertTrue($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition for path without an alias.
   *
   * @covers ::evaluate
   */
  public function testConditionEvaluationPathWithoutAlias() {
    $this->aliasManager->getAliasByPath('path-without-alias', NULL)
      ->willReturn('path-without-alias')
      ->shouldBeCalledTimes(1);

    $this->aliasManager->getAliasByPath('path-without-alias', 'en')
      ->willReturn('path-without-alias')
      ->shouldBeCalledTimes(1);

    // First, only set the path context.
    $this->condition->setContextValue('path', 'path-without-alias');

    // Test without language context set.
    $this->assertFalse($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->englishLanguage->reveal());
    $this->assertFalse($this->condition->evaluate());
  }

}
