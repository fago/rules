<?php

/**
 * @file
 * Contains Drupal\Tests\rules\Kernel\TypedData\DataFetcherTest.
 */

namespace Drupal\Tests\rules\Kernel\TypedData;

use \Drupal\KernelTests\KernelTestBase;

/**
 * Class DataFetcherTest.
 *
 * @group rules
 *
 * @cover \Drupal\rules\TypedData\DataFetcher
 */
class DataFetcherTest extends KernelTestBase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\rules\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * A node used for testing.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules', 'system', 'node', 'field', 'text', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->typedDataManager = $this->container->get('typed_data_manager');

    $entity_type_manager = $this->container->get('entity_type.manager');
    $entity_type_manager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    $this->node = $entity_type_manager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
      ]);
  }

  /**
   * @cover fetchByPropertyPath
   */
  public function testFetchingByBasicPropertyPath() {
    $this->assertEquals(
      $this->node->title->value,
      $this->typedDataManager->getDataFetcher()
      ->fetchByPropertyPath($this->node->getTypedData(), 'title.0.value')
      ->getValue()
    );
  }

}
