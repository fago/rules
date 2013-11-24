<?php

/**
 * @file
 * Contains Drupal\rules\tests\EngineTest.
 */

namespace Drupal\rules\tests;

use Drupal\Tests\UnitTestCase;

/**
 * Tests the core rules engine functionality.
 */
class EngineTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Rules Engine tests',
      'description' => 'Test using the rules API to create and evaluate rules.',
      'group' => 'Rules',
    );
  }

  public function testRuleCreation() {
    $this->fail('foo');
  }
}
