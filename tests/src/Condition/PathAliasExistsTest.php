<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\PathAliasExistsTest.
 */

namespace Drupal\rules\Tests\Condition;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\rules\Plugin\Condition\PathAliasExists;
use Drupal\rules\Tests\RulesTestBase;

/**
 * Tests the 'Path alias exists' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\PathAliasExists
 *
 * @see \Drupal\rules\Plugin\Condition\PathAliasExists
 */
class PathAliasExistsTest extends RulesTestBase {

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
  public static function getInfo() {
    return [
      'name' => 'Path alias exists condition test',
      'description' => 'Tests the condition.',
      'group' => 'Rules conditions',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->condition = new PathAliasExists([], '', ['context' => [
      'alias' => new ContextDefinition('string'),
      'language' => new ContextDefinition('language', FALSE),
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
    $this->assertEquals('Path alias exists', $this->condition->summary());
  }

  /**
   * Tests evaluating the condition for an alias that can be resolved.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluationAliasWithPath() {
    $this->aliasManager->expects($this->at(0))
      ->method('getPathByAlias')
      ->with('alias-for-path', $this->anything())
      ->will($this->returnValue('path-with-alias'));

    $this->aliasManager->expects($this->at(1))
      ->method('getPathByAlias')
      ->with('alias-for-path', 'en')
      ->will($this->returnValue('path-with-alias'));

    // First, only set the path context.
    $this->condition->setContextValue('alias', $this->getMockTypedData('alias-for-path'));

    // Test without language context set.
    $this->assertTrue($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->getMockTypedData($this->englishLanguage));
    $this->assertTrue($this->condition->evaluate());
  }

  /**
   * Tests evaluating the condition for an alias that can not be resolved.
   *
   * @covers ::evaluate()
   */
  public function testConditionEvaluationAliasWithoutPath() {
    $this->aliasManager->expects($this->at(0))
      ->method('getPathByAlias')
      ->with('alias-for-path-that-does-not-exist', $this->anything())
      ->will($this->returnValue('alias-for-path-that-does-not-exist'));

    $this->aliasManager->expects($this->at(1))
      ->method('getPathByAlias')
      ->with('alias-for-path-that-does-not-exist', 'en')
      ->will($this->returnValue('alias-for-path-that-does-not-exist'));

    // First, only set the path context.
    $this->condition->setContextValue('alias', $this->getMockTypedData('alias-for-path-that-does-not-exist'));

    // Test without language context set.
    $this->assertFalse($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->getMockTypedData($this->englishLanguage));
    $this->assertFalse($this->condition->evaluate());
  }

}
