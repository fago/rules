<?php

/**
 * @file
 * Contains Drupal\Tests\rules\Kernel\TypedData\DataFetcherTest.
 */

namespace Drupal\Tests\rules\Kernel\TypedData;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

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
   * An entity type manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

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

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->entityTypeManager->getStorage('node_type')
      ->create(['type' => 'page'])
      ->save();

    $this->node = $this->entityTypeManager->getStorage('node')
      ->create([
        'title' => 'test',
        'type' => 'page',
      ]);

    $this->installEntitySchema('entity_field_test');
    $this->createTestField(
      'node',
      'page',
      'field_test',
      'Field test',
      'entity_field_test',
      'default',
      array('target_bundles' => array('page')),
      FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
    );

    $this->installSchema('system', ['sequences']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
  }

  /**
   * Creates a test field of test field storage on the specified bundle.
   *
   * @param string $entity_type
   *   The type of entity the field will be attached to.
   * @param string $bundle
   *   The bundle name of the entity the field will be attached to.
   * @param string $field_name
   *   The name of the field; if it already exists, a new instance of the existing
   *   field will be created.
   * @param string $field_label
   *   The label of the field.
   * @param string $target_entity_type
   *   The type of the referenced entity.
   * @param string $selection_handler
   *   The selection handler used by this field.
   * @param array $selection_handler_settings
   *   An array of settings supported by the selection handler specified above.
   *   (e.g. 'target_bundles', 'sort', 'auto_create', etc).
   * @param int $cardinality
   *   The cardinality of the field.
   *
   * @see \Drupal\Core\Entity\Plugin\EntityReferenceSelection\SelectionBase::buildConfigurationForm()
   */
  protected function createTestField($entity_type, $bundle, $field_name, $field_label, $target_entity_type, $selection_handler = 'default', $selection_handler_settings = array(), $cardinality = 1) {
    // Look for or add the specified field to the requested entity bundle.
    if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
      FieldStorageConfig::create(array(
        'field_name' => $field_name,
        'type' => 'test',
        'entity_type' => $entity_type,
        'cardinality' => $cardinality,
        'settings' => array(
          'target_type' => $target_entity_type,
        ),
      ))->save();
    }
    if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
      FieldConfig::create(array(
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'bundle' => $bundle,
        'label' => $field_label,
        'settings' => array(
          'handler' => $selection_handler,
          'handler_settings' => $selection_handler_settings,
        ),
      ))->save();
    }
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

  /**
   * @cover fetchBySubPaths
   */
  public function testFetchingByBasicSubPath() {
    $this->assertEquals(
      $this->node->title->value,
      $this->typedDataManager->getDataFetcher()
      ->fetchBySubPaths($this->node->getTypedData(), array('title', '0', 'value'))
      ->getValue()
    );
  }

  /**
   * @cover fetchByPropertyPath
   */
  public function testFetchingEntityReference() {
    $user = $this->entityTypeManager->getStorage('user')
      ->create([
        'name' => 'test',
        'type' => 'user',
      ]);
    $this->node->uid->target_id = $user->id();
    $fetched_user = $this->typedDataManager->getDataFetcher()
      ->fetchByPropertyPath($this->node->getTypedData(), 'uid.entity')
      ->getValue();
    $this->assertInstanceof('\Drupal\user\UserInterface', $fetched_user instanceof EntityAdapter);
  }

  /**
   * @cover fetchByPropertyPath
   */
  public function _testFetchingValueAtPosition0() {
    /** @var \Drupal\Core\Field\FieldItemBase  $field_item_c */
    #$field_item_c = $this->getMockForAbstractClass('Drupal\Core\Field\FieldItemBase', [], '', FALSE);
    $this->node->field_test->setValue(['0' => 1, '1' => 2]);

    $fetched_user = $this->typedDataManager->getDataFetcher()
      ->fetchByPropertyPath($this->node->getTypedData(), 'uid.0.entity')
      ->getValue();
    $this->assertInstanceof('\Drupal\user\UserInterface', $fetched_user instanceof EntityAdapter);
  }

  /**
   * @cover fetchByPropertyPath
   */
  public function _testFetchingEntityReferenceAtPosition1() {

  }

  /**
   * @cover fetchByPropertyPath
   */
  public function testFetchingNonExistingEntityReference() {
    $this->setExpectedException('Drupal\Core\TypedData\Exception\MissingDataException');
    $fetched_user = $this->typedDataManager->getDataFetcher()
      ->fetchByPropertyPath($this->node->getTypedData(), 'uid.0.entity')
      ->getValue();
    $this->assertEquals(TRUE, $fetched_user instanceof EntityAdapter);
  }

}
