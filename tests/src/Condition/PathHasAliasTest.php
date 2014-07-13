<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\PathHasAliasTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\PathHasAlias;
use Drupal\rules\Tests\RulesTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\PathHasAlias
 * @group rules_conditions
 */
class PathHasAliasTest extends RulesTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Engine\RulesConditionInterface
   */
  protected $condition;

  /**
   * The mocked alias manager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * A mocked language object (english).
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Language\LanguageInterface
   */
  protected $englishLanguage;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->condition = new PathHasAlias([], '', ['context' => [
      'path' => new ContextDefinition('string'),
      'language' => new ContextDefinition('string', NULL, FALSE),
    ]], $this->aliasManager);

    $this->condition->setStringTranslation($this->getMockStringTranslation());

    $this->englishLanguage = $this->getMock('Drupal\Core\Language\LanguageInterface');
    $this->englishLanguage->expects($this->any())
      ->method('getId')
      ->will($this->returnValue('en'));
  }

  /**
   * Tests that the dependencies are properly set in the constructor.
   *
   * @covers ::__construct()
   */
  public function testConstructor() {
    $property = new \ReflectionProperty($this->condition, 'aliasManager');
    $property->setAccessible(TRUE);

    $this->assertSame($this->aliasManager, $property->getValue($this->condition));
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary()
   */
  public function testSummary() {
    $this->assertEquals('Path has alias', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition for a path with an alias.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluationPathWithAlias() {
    $this->aliasManager->expects($this->at(0))
      ->method('getAliasByPath')
      ->with('path-with-alias', $this->anything())
      ->will($this->returnValue('alias-for-path'));

    $this->aliasManager->expects($this->at(1))
      ->method('getAliasByPath')
      ->with('path-with-alias', 'en')
      ->will($this->returnValue('alias-for-path'));

    // First, only set the path context.
    $this->condition->setContextValue('path', $this->getMockTypedData('path-with-alias'));

    // Test without language context set.
    $this->assertTrue($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->getMockTypedData($this->englishLanguage));
    $this->assertTrue($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition for path without an alias.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluationPathWithoutAlias() {
    $this->aliasManager->expects($this->at(0))
      ->method('getAliasByPath')
      ->with('path-without-alias', $this->anything())
      ->will($this->returnValue('path-without-alias'));

    $this->aliasManager->expects($this->at(1))
      ->method('getAliasByPath')
      ->with('path-without-alias', 'en')
      ->will($this->returnValue('path-without-alias'));

    // First, only set the path context.
    $this->condition->setContextValue('path', $this->getMockTypedData('path-without-alias'));

    // Test without language context set.
    $this->assertFalse($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->getMockTypedData($this->englishLanguage));
    $this->assertFalse($this->condition->evaluate());
  }

}
