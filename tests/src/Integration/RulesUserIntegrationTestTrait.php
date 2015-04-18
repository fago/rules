<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\RulesUserIntegrationTestTrait.
 */

namespace Drupal\Tests\rules\Integration;

/**
 * Trait for Rules integration tests with user entities.
 */
trait RulesUserIntegrationTestTrait {

  /**
   * Creates a mocked user.
   *
   * @return \Drupal\user\UserInterface|\PHPUnit_Framework_MockObject_MockObject
   *   The mocked user.
   */
  protected function getMockedUser() {
    return $this->getMock('Drupal\user\UserInterface');
  }

  /**
   * Creates a mocked user role.
   *
   * @param string $name
   *   The machine-readable name of the mocked role.
   *
   * @return \Drupal\user\RoleInterface|\PHPUnit_Framework_MockObject_MockBuilder
   *   The mocked role.
   */
  protected function getMockedUserRole($name) {
    $role = $this->getMockBuilder('Drupal\user\RoleInterface')
      ->getMock();

    $role->expects($this->any())
      ->method('id')
      ->will($this->returnValue($name));

    return $role;
  }

}
