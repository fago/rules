<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\RulesIntegrationTestBase.
 */

namespace Drupal\Tests\rules\Integration;

use Drupal\Core\Action\ActionManager;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\Cache\NullBackend;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\Tests\rules\Unit\RulesUnitTestBase;
use Drupal\rules\Plugin\RulesDataProcessorManager;
use Drupal\rules\Plugin\RulesExpressionPluginManager;

/**
 * Base class for Rules integration tests.
 *
 * Rules integration tests leverage the services (plugin managers) of the Rules
 * module to test the integration of an action or condition. Dependencies on
 * other 3rd party modules or APIs can and should be mocked; e.g. the action
 * to delete an entity would mock the call to the entity API.
 */
abstract class RulesIntegrationTestBase extends RulesUnitTestBase {

  /**
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * @var \Drupal\Core\Action\ActionManager
   */
  protected $actionManager;

  /**
   * @var \Drupal\Core\Path\AliasManager
   */
  protected $aliasManager;

  /**
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * @var \Drupal\rules\Plugin\RulesExpressionPluginManager
   */
  protected $rulesExpressionManager;

  /**
   * @var \Drupal\rules\Plugin\RulesDataProcessorManager
   */
  protected $rulesDataProcessorManager;

  /**
   * All setup'ed namespaces.
   *
   * @var ArrayObject
   */
  protected $namespaces;

  /**
   * @var \Drupal\Core\Cache\NullBackend
   */
  protected $cacheBackend;


  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Defines extra namespaces used for finding plugins.
   *
   * @var String[]
   */
  protected $extraNamespaces = [];

  /**
   * Array keyed with module names and TRUE as value.
   *
   * @var boolean[]
   */
  protected $enabledModules;

  /**
   * @var \Drupal\Core\DependencyInjection\Container
   */
  protected $container;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $container = new ContainerBuilder();
    // Register plugin managers used by Rules, but mock some unwanted
    // dependencies requiring more stuff to loaded.
    $this->moduleHandler = $this->getMockBuilder('Drupal\Core\Extension\ModuleHandlerInterface')
      ->disableOriginalConstructor()
      ->getMock();
    // Set all the modules as being existent.
    $this->enabledModules['rules'] = TRUE;
    $this->enabledModules['rules_test'] = TRUE;
    $this->moduleHandler->expects($this->any())
      ->method('moduleExists')
      ->will($this->returnValueMap(array_map(function($module) {
       return [$module, TRUE];
    }, array_keys($this->enabledModules))));

    $this->cacheBackend = new NullBackend('rules');
    $rules_directory = __DIR__ . '/../../..';
    $this->namespaces = new \ArrayObject(array(
      'Drupal\\rules' => $rules_directory . '/src',
      'Drupal\\rules_test' => $rules_directory . '/tests/modules/rules_test/src',
      'Drupal\\Core\\TypedData' => $this->root . '/core/lib/Drupal/Core/TypedData',
      'Drupal\\Core\\Validation' => $this->root . '/core/lib/Drupal/Core/Validation',
    ) + $this->extraNamespaces);

    $this->actionManager = new ActionManager($this->namespaces, $this->cacheBackend, $this->moduleHandler);
    $this->conditionManager = new ConditionManager($this->namespaces, $this->cacheBackend, $this->moduleHandler);
    $this->rulesExpressionManager = new RulesExpressionPluginManager($this->namespaces, $this->moduleHandler);
    $this->typedDataManager = new TypedDataManager($this->namespaces, $this->cacheBackend, $this->moduleHandler);
    $this->rulesDataProcessorManager = new RulesDataProcessorManager($this->namespaces, $this->moduleHandler);

    $this->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $container->set('path.alias_manager', $this->aliasManager);
    $container->set('plugin.manager.action', $this->actionManager);
    $container->set('plugin.manager.condition', $this->conditionManager);
    $container->set('plugin.manager.rules_expression', $this->rulesExpressionManager);
    $container->set('plugin.manager.rules_data_processor', $this->rulesExpressionManager);
    $container->set('typed_data_manager', $this->typedDataManager);
    $container->set('string_translation', $this->getStringTranslationStub());

    \Drupal::setContainer($container);
    $this->container = $container;
  }

  /**
   * Returns a typed data object.
   *
   * This helper for quick creation of typed data objects.
   *
   * @param string $data_type
   *   The data type to create an object for.
   * @param mixed[] $value
   *   The value to set.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The created object.
   */
  protected function getTypedData($data_type, $value)  {
    $definition = $this->typedDataManager->createDataDefinition($data_type);
    $data = $this->typedDataManager->create($definition);
    $data->setValue($value);
    return $data;
  }

}
