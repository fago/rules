<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\RulesIntegrationTestBase.
 */

namespace Drupal\Tests\rules\Integration;

use Drupal\Core\Action\ActionManager;
use Drupal\Core\Cache\NullBackend;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Context\DataProcessorManager;
use Drupal\rules\Engine\ExpressionPluginManager;
use Drupal\Tests\UnitTestCase;

/**
 * Base class for Rules integration tests.
 *
 * Rules integration tests leverage the services (plugin managers) of the Rules
 * module to test the integration of an action or condition. Dependencies on
 * other 3rd party modules or APIs can and should be mocked; e.g. the action
 * to delete an entity would mock the call to the entity API.
 */
abstract class RulesIntegrationTestBase extends UnitTestCase {

  /**
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

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
   * @var \Drupal\rules\Engine\ExpressionPluginManager
   */
  protected $rulesExpressionManager;

  /**
   * @var \Drupal\rules\Context\DataProcessorManager
   */
  protected $rulesDataProcessorManager;

  /**
   * All setup'ed namespaces.
   *
   * @var \ArrayObject
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
   * Array object keyed with module names and TRUE as value.
   *
   * @var \ArrayObject
   */
  protected $enabledModules;

  /**
   * @var \Drupal\Core\DependencyInjection\Container
   */
  protected $container;

  /**
   * The class resolver mock for the typed data manager.
   *
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface
   */
  protected $classResolver;

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
    $this->enabledModules = new \ArrayObject();
    $this->enabledModules['rules'] = TRUE;
    $this->enabledModules['rules_test'] = TRUE;
    $this->moduleHandler->expects($this->any())
      ->method('moduleExists')
      ->will($this->returnCallback(function ($module) {
        return [$module, $this->enabledModules[$module]];
      }));

    $this->cacheBackend = new NullBackend('rules');
    $rules_directory = __DIR__ . '/../../..';
    $this->namespaces = new \ArrayObject([
      'Drupal\\rules' => $rules_directory . '/src',
      'Drupal\\rules_test' => $rules_directory . '/tests/modules/rules_test/src',
      'Drupal\\Core\\TypedData' => $this->root . '/core/lib/Drupal/Core/TypedData',
      'Drupal\\Core\\Validation' => $this->root . '/core/lib/Drupal/Core/Validation',
    ]);

    $this->actionManager = new ActionManager($this->namespaces, $this->cacheBackend, $this->moduleHandler);
    $this->conditionManager = new ConditionManager($this->namespaces, $this->cacheBackend, $this->moduleHandler);
    $this->rulesExpressionManager = new ExpressionPluginManager($this->namespaces, $this->moduleHandler);

    $this->classResolver = $this->getMockBuilder('Drupal\Core\DependencyInjection\ClassResolverInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->typedDataManager = new TypedDataManager($this->namespaces, $this->cacheBackend, $this->moduleHandler, $this->classResolver);
    $this->rulesDataProcessorManager = new DataProcessorManager($this->namespaces, $this->moduleHandler);

    $this->aliasManager = $this->getMockBuilder('Drupal\Core\Path\AliasManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    $this->entityManager = $this->getMockBuilder('Drupal\Core\Entity\EntityManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $this->entityManager->expects($this->any())
      ->method('getDefinitions')
      ->willReturn([]);

    $container->set('entity.manager', $this->entityManager);
    $container->set('path.alias_manager', $this->aliasManager);
    $container->set('plugin.manager.action', $this->actionManager);
    $container->set('plugin.manager.condition', $this->conditionManager);
    $container->set('plugin.manager.rules_expression', $this->rulesExpressionManager);
    $container->set('plugin.manager.rules_data_processor', $this->rulesDataProcessorManager);
    $container->set('typed_data_manager', $this->typedDataManager);
    $container->set('string_translation', $this->getStringTranslationStub());

    \Drupal::setContainer($container);
    $this->container = $container;
  }

  /**
   * Fakes the enabling of a module and adds its namespace for plugin loading.
   *
   * Default behaviour works fine for core modules.
   *
   * @param string $name
   *   The name of the module that's gonna be enabled.
   * @param array $namespaces
   *   Map of the association between module's namespaces and filesystem paths.
   */
  protected function enableModule($name, array $namespaces = []) {
    $this->enabledModules[$name] = TRUE;

    if (empty($namespaces)) {
      $namespaces = ['Drupal\\' . $name => $this->root . '/core/modules/' . $name . '/src'];
    }
    foreach ($namespaces as $namespace => $path) {
      $this->namespaces[$namespace] = $path;
    }
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
