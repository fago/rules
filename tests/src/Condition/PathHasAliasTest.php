<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\Condition\PathHasAliasTest.
 */

namespace Drupal\rules\Tests\Condition;
use Drupal\rules\Plugin\Condition\PathHasAlias;

/**
 * Tests the 'Path has alias' condition.
 *
 * @coversDefaultClass \Drupal\rules\Plugin\Condition\PathHasAlias
 *
 * @see \Drupal\rules\Plugin\Condition\PathHasAlias
 */
class PathHasAliasTest extends ConditionTestBase {

  /**
   * The mocked alias manager.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The mocked condition to be tested.
   *
   * @var \PHPUnit_Framework_MockObject_MockObject|\Drupal\rules\Plugin\Condition\PathHasAlias
   */
  protected $condition;

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
      'name' => 'Path has alias condition test',
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

    $this->condition = new PathHasAlias([], '', [], $this->typedDataManager, $this->aliasManager);
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
    // Test that the 'path' context is properly defined.
    $path = $this->condition->getContext('path');
    $this->assertInstanceOf('Drupal\rules\Context\ContextInterface', $path);
    $definition = $path->getContextDefinition();
    $this->assertInstanceOf('Drupal\rules\Context\ContextDefinitionInterface', $definition);

    // Test the specific context definition properties.
    $this->assertEquals('Path', $definition->getLabel());
    $this->assertEquals('string', $definition->getDataType());
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
    $this->assertEquals('Path has alias', $this->condition->summary());
  }

  /**
   * Tests context value setting and getting.
   *
   * @covers ::setContextValue()
   * @covers ::getContextValue()
   */
  public function testContextValues() {
    // Test setting and getting context values.
    $this->assertSame($this->condition, $this->condition->setContextValue('path', 'user'));
    $this->assertSame('user', $this->condition->getContextValue('path'));
    $this->assertSame($this->condition, $this->condition->setContextValue('language', $this->englishLanguage));
    $this->assertSame($this->englishLanguage, $this->condition->getContextValue('language'));
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
    $this->condition->setContextValue('path', 'path-with-alias');

    // Test without language context set.
    $this->assertTrue($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->englishLanguage);
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
    $this->condition->setContextValue('path', 'path-without-alias');

    // Test without language context set.
    $this->assertFalse($this->condition->evaluate());

    // Test with language context set.
    $this->condition->setContextValue('language', $this->englishLanguage);
    $this->assertFalse($this->condition->evaluate());
  }

}
