<?php

/**
 * @file
 * Contains \Drupal\rules\Tests\RulesConditionContainerTest.
 */

namespace Drupal\rules\Tests;

/**
 * @coversDefaultClass \Drupal\rules\Engine\RulesConditionContainer
 * @group rules
 */
class RulesConditionContainerTest extends RulesUnitTestBase {

  /**
   * Creates a mocked condition container.
   *
   * @param array $methods
   *   The methods to mock.
   * @param string $class
   *   The name of the created mock class.
   *
   * @return \Drupal\rules\Engine\RulesConditionContainerInterface
   *   The mocked condition container.
   */
  protected function getMockConditionContainer(array $methods = [], $class = 'RulesConditionContainerMock') {
    return $this->getMockForAbstractClass(
      'Drupal\rules\Engine\RulesConditionContainer', [], $class, FALSE, TRUE, TRUE, $methods
    );
  }

  /**
   * Tests adding conditions to the condition container.
   *
   * @covers ::addCondition()
   */
  public function testAddCondition() {
    $container = $this->getMockConditionContainer();
    $container->addCondition($this->trueCondition);

    $property = new \ReflectionProperty($container, 'conditions');
    $property->setAccessible(TRUE);

    $this->assertArrayEquals([$this->trueCondition], $property->getValue($container));
  }

  /**
   * Tests negating the result of the condition container.
   *
   * @covers ::negate()
   * @covers ::isNegated()
   */
  public function testNegate() {
    $container = $this->getMockConditionContainer(['evaluate']);
    $container->expects($this->exactly(2))
      ->method('evaluate')
      ->will($this->returnValue(TRUE));

    $this->assertFalse($container->isNegated());
    $this->assertTrue($container->execute());

    $container->negate(TRUE);
    $this->assertTrue($container->isNegated());
    $this->assertFalse($container->execute());
  }

  /**
   * Tests executing the condition container.
   *
   * @covers ::execute()
   */
  public function testExecute() {
    $container = $this->getMockConditionContainer(['evaluate']);
    $container->expects($this->once())
      ->method('evaluate')
      ->will($this->returnValue(TRUE));

    $this->assertTrue($container->execute());
  }

  /**
   * Tests executing the condition container with an executable manager..
   *
   * @covers ::execute()
   */
  public function testExecuteWithExecutableManager() {
    $container = $this->getMockConditionContainer(['evaluate']);
    $container->expects($this->never())
      ->method('evaluate');

    $manager = $this->getMock('Drupal\Core\Executable\ExecutableManagerInterface');
    $manager->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));

    $container->setExecutableManager($manager);
    $this->assertTrue($container->execute());
  }

}
