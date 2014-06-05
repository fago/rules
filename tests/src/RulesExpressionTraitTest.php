<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesExpressionTraitTest.
 */

namespace Drupal\rules\Tests;

/**
 * Tests \Drupal\rules\RulesExpressionTrait.
 *
 * @coversDefaultClass \Drupal\rules\RulesExpressionTrait
 *
 * @see \Drupal\rules\RulesExpressionTrait
 */
class RulesExpressionTraitTest extends RulesTestBase {

  /**
   * A reflection of self::$traitObject.
   *
   * @var \ReflectionClass
   */
  protected $reflection;

  /**
   * The mocked trait object.
   *
   * @var object
   */
  protected $traitObject;

  /**
   * The mocked expression manager.
   *
   * @var \Drupal\rules\Plugin\RulesExpressionPluginManager.
   */
  protected $expressionManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Rules expression trait',
      'description' => 'Tests the rules expression trait.',
      'group' => 'Rules',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->traitObject = $this->getObjectForTrait('\Drupal\rules\RulesExpressionTrait');

    $this->expressionManager = $this->getMockBuilder('\Drupal\rules\Plugin\RulesExpressionPluginManager')
      ->setMethods(['createInstance'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->expressionManager->expects($this->any())
      ->method('createInstance')
      ->will($this->returnCallback(function ($id) {
        $type = ucfirst(substr($id, 6));
        return $this->{"getMock$type"}();
      }));

    $this->traitObject->setRulesExpressionManager($this->expressionManager);
    $this->reflection = new \ReflectionClass($this->traitObject);
  }

  /**
   * Tests the the rules expression manager setter.
   *
   * @covers ::setRulesExpressionManager()
   */
  public function testSetRulesExpressionManager() {
    $this->assertSame($this->traitObject, $this->traitObject->setRulesExpressionManager($this->expressionManager));
  }

  /**
   * Tests the the rules expression manager getter.
   *
   * @covers ::getRulesExpressionManager()
   */
  public function testGetRulesExpressionManager() {
    $this->assertSame($this->expressionManager, $this->traitObject->getRulesExpressionManager());
  }

  /**
   * Tests object creation using the convenience methods on the trait.
   *
   * @covers ::createRulesExpression()
   * @covers ::createRulesRule()
   * @covers ::createRulesAnd()
   * @covers ::createRulesOr()
   *
   * @dataProvider convenienceMethodsDataProvider
   *
   * @todo Test ::createRulesAction() and ::createRulesCondition().
   */
  public function testConvenienceMethods($id, $method, $class) {
    // Test the 'createRulesExpression' method.
    $reflection = $this->reflection->getMethod('createRulesExpression');
    $reflection->setAccessible(TRUE);
    $object = $reflection->invokeArgs($this->traitObject, [$id]);
    $this->assertInstanceOf($class, $object);

    // Test the specific convenience method (e.g. 'createRule').
    $reflection = $this->reflection->getMethod($method);
    $reflection->setAccessible(TRUE);
    $object = $reflection->invoke($this->traitObject);
    $this->assertInstanceOf($class, $object);
  }

  /**
   * Provides a list of convenience methods and their expected return values.
   */
  public function convenienceMethodsDataProvider() {
    return [
      ['rules_and', 'createRulesAnd', '\Drupal\rules\Plugin\RulesExpression\RulesAnd'],
      ['rules_or', 'createRulesOr', '\Drupal\rules\Plugin\RulesExpression\RulesOr'],
      ['rules_rule', 'createRulesRule', '\Drupal\rules\Plugin\RulesExpression\Rule'],
    ];
  }

}
