<?php

/**
 * @file
 * Contains Drupal\Tests\rules\Kernel\TypedData\DataFiterTest.
 */

namespace Drupal\Tests\rules\Kernel\TypedData;

use Drupal\Core\TypedData\DataDefinition;
use Drupal\KernelTests\KernelTestBase;


/**
 * Tests using typed data filters.
 *
 * @group rules
 *
 * @cover \Drupal\rules\TypedData\DataFilterManager
 */
class DataFiterTest extends KernelTestBase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\rules\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * The data filter manager.
   *
   * @var \Drupal\rules\TypedData\DataFilterManagerInterface
   */
  protected $dataFilterManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['rules', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->typedDataManager = $this->container->get('typed_data_manager');
    $this->dataFilterManager = $this->container->get('plugin.manager.typed_data_filter');
  }

  /**
   * @cover \Drupal\rules\Plugin\TypedDataFilter\LowerFilter
   */
  public function testLowerFilter() {
    $filter = $this->dataFilterManager->createInstance('lower');
    $data = $this->typedDataManager->create(DataDefinition::create('string'), 'tEsT');

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertEquals('string', $filter->filtersTo($data->getDataDefinition(), [])->getDataType());

    $this->assertEquals('test', $filter->filter($data->getDataDefinition(), $data->getValue(), []));
  }

  /**
   * @cover \Drupal\rules\Plugin\TypedDataFilter\DefaultFilter
   */
  public function testDefaultFilter() {
    $filter = $this->dataFilterManager->createInstance('default');
    $data = $this->typedDataManager->create(DataDefinition::create('string'));

    $this->assertTrue($filter->canFilter($data->getDataDefinition()));
    $this->assertFalse($filter->canFilter(DataDefinition::create('any')));

    $this->assertSame($data->getDataDefinition(), $filter->filtersTo($data->getDataDefinition(), ['default']));

    $fails = $filter->validateArguments($data->getDataDefinition(), []);
    $this->assertEquals(1, count($fails));
    $this->assertContains('Missing arguments', (string) $fails[0]);
    $fails = $filter->validateArguments($data->getDataDefinition(), [new \StdClass()]);
    $this->assertEquals(1, count($fails));
    $this->assertEquals('This value should be of the correct primitive type.', $fails[0]);

    $this->assertEquals('default', $filter->filter($data->getDataDefinition(), $data->getValue(), ['default']));
    $data->setValue('non-default');
    $this->assertEquals('non-default', $filter->filter($data->getDataDefinition(), $data->getValue(), ['default']));
  }

}
