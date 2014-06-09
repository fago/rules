<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\PathAliasExistsTest.
 */

namespace Drupal\rules\Tests\Condition;
use Drupal\rules\Plugin\Condition\PathAliasExists;

/**
 * Tests the 'Path alias exists' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\PathAliasExists
 *
 * @see \Drupal\rules\Plugin\Condition\PathAliasExists
 */
class PathAliasExistsTest extends ConditionTestBase {

  /**
   * The condition to be tested.
   *
   * @var \Drupal\rules\Plugin\Condition\PathAliasExists
   */
  protected $condition;

  /**
   * The mocked alias manager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The mocked typed data manager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

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

    $this->typedDataManager = $this->getMockTypedDataManager();
    $this->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->condition = new PathAliasExists([], '', [], $this->typedDataManager, $this->aliasManager);
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
    $this->assertSame($this->typedDataManager, $this->condition->getTypedDataManager());
  }

  /**
   * Tests the context definitions.
   *
   * @covers ::contextDefinitions()
   */
  public function testContextDefinition() {
    // Test that the 'alias' context is properly defined.
    $alias = $this->condition->getContext('alias');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $alias);
    $definition = $alias->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Path alias', $definition->getLabel());
    $this->assertEquals('string', $definition->getDataType());
    $this->assertEquals("Specify the path alias to check for. For example, 'about' for an about page.", $definition->getDescription());
    $this->assertTrue($definition->isRequired());

    // Test that the 'language' is properly defined.
    $language = $this->condition->getContext('language');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $language);
    $definition = $language->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Language', $definition->getLabel());
    $this->assertEquals('language', $definition->getDataType());
    $this->assertEquals('If specified, the language for which the URL alias applies.', $definition->getDescription());
    $this->assertFalse($definition->isRequired());
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
   * Tests context value setting and getting.
   *
   * @covers ::setContextValue()
   * @covers ::getContextValue()
   */
  public function testContextValues() {
    // Test setting and getting context values.
    $this->assertSame($this->condition, $this->condition->setContextValue('alias', 'my-alias'));
    $this->assertSame('my-alias', $this->condition->getContextValue('alias'));
    $this->assertSame($this->condition, $this->condition->setContextValue('language', $this->englishLanguage));
    $this->assertSame($this->englishLanguage, $this->condition->getContextValue('language'));
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
    $this->condition->setContextValue('alias', 'alias-for-path');

    // Test without language context set.
    $this->assertTrue($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->englishLanguage);
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
    $this->condition->setContextValue('alias', 'alias-for-path-that-does-not-exist');

    // Test without language context set.
    $this->assertFalse($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->englishLanguage);
    $this->assertFalse($this->condition->evaluate());
  }

}
