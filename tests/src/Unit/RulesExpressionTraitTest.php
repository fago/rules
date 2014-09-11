<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesExpressionTraitTest.
 */

namespace Drupal\Tests\rules\Unit;

/**
 * @coversDefaultClass \Drupal\rules\RulesExpressionTrait
 * @group rules
 */
class RulesExpressionTraitTest extends RulesUnitTestBase {

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
  public function setUp() {
    $this->traitObject = $this->getObjectForTrait('\Drupal\rules\RulesExpressionTrait');
    $this->reflection = new \ReflectionClass($this->traitObject);
  }

  /**
   * Tests the the rules expression manager setter.
   *
   * @covers ::setRulesExpressionManager()
   * @covers ::getRulesExpressionManager()
   */
  public function testSetAndGetRulesExpressionManager() {
    $manager = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpressionPluginManager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertSame($this->traitObject, $this->traitObject->setRulesExpressionManager($manager));
    $this->assertSame($manager, $this->traitObject->getRulesExpressionManager());
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
  public function testConvenienceMethods($id, $method_name, $expected_object) {
    $manager = $this->getMockBuilder('Drupal\rules\Plugin\RulesExpressionPluginManager')
      ->setMethods(['createInstance'])
      ->disableOriginalConstructor()
      ->getMock();

    $manager->expects($this->any())
      ->method('createInstance')
      ->with($id, $this->anything())
      ->will($this->returnValue($expected_object));

    $this->traitObject->setRulesExpressionManager($manager);

    // Test the 'createRulesExpression' method.
    $method = $this->reflection->getMethod('createRulesExpression');
    $method->setAccessible(TRUE);
    $object = $method->invokeArgs($this->traitObject, [$id]);
    $this->assertSame($expected_object, $object);

    // Test the specific convenience method (e.g. 'createRule').
    $method = $this->reflection->getMethod($method_name);
    $method->setAccessible(TRUE);
    $object = $method->invoke($this->traitObject);
    $this->assertSame($expected_object, $object);
  }

  /**
   * Provides a list of convenience methods and their expected return values.
   */
  public function convenienceMethodsDataProvider() {
    return [
      ['rules_and', 'createRulesAnd', $this->getMockAnd()],
      ['rules_or', 'createRulesOr', $this->getMockOr()],
      ['rules_rule', 'createRulesRule', $this->getMockRule()],
    ];
  }

}
