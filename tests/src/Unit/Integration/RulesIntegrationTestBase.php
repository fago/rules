<?php

namespace Drupal\Tests\rules\Unit\Integration;

use Drupal\Component\Uuid\Php;
use Drupal\Core\Cache\NullBackend;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Plugin\Context\LazyContextRepository;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Core\ConditionManager;
use Drupal\rules\Context\DataProcessorManager;
use Drupal\rules\Core\RulesActionManager;
use Drupal\rules\Engine\ExpressionManager;
use Drupal\typed_data\DataFetcher;
use Drupal\typed_data\DataFilterManager;
use Drupal\typed_data\PlaceholderResolver;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

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
   * @var \Drupal\Core\Entity\EntityManagerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $entityManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $entityFieldManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $entityTypeBundledInfo;

  /**
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * @var \Drupal\rules\Core\RulesActionManagerInterface
   */
  protected $actionManager;

  /**
   * @var \Drupal\Core\Path\AliasManager|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $aliasManager;

  /**
   * @var \Drupal\rules\Core\ConditionManager
   */
  protected $conditionManager;

  /**
   * @var \Drupal\rules\Engine\ExpressionManager
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
   * @var \Drupal\Core\Extension\ModuleHandlerInterface||\Prophecy\Prophecy\ProphecyInterface
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
   * @var \Drupal\Core\DependencyInjection\ClassResolverInterface|\Prophecy\Prophecy\ProphecyInterface
   */
  protected $classResolver;

  /**
   * The data fetcher service.
   *
   * @var \Drupal\typed_data\DataFetcher
   */
  protected $dataFetcher;

  /**
   * The placeholder resolver service.
   *
   * @var \Drupal\typed_data\PlaceholderResolver
   */
  protected $placeholderResolver;

  /**
   * The data filter manager.
   *
   * @var \Drupal\typed_data\DataFilterManager
   */
  protected $dataFilterManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $container = new ContainerBuilder();
    // Register plugin managers used by Rules, but mock some unwanted
    // dependencies requiring more stuff to loaded.
    $this->moduleHandler = $this->prophesize(ModuleHandlerInterface::class);

    // Set all the modules as being existent.
    $this->enabledModules = new \ArrayObject();
    $this->enabledModules['rules'] = TRUE;
    $this->enabledModules['rules_test'] = TRUE;
    $enabled_modules = $this->enabledModules;
    $this->moduleHandler->moduleExists(Argument::type('string'))
      ->will(function ($arguments) use ($enabled_modules) {
        return [$arguments[0], $enabled_modules[$arguments[0]]];
      });

    // Wed don't care about alter() calls on the module handler.
    $this->moduleHandler->alter(Argument::any(), Argument::any(), Argument::any(), Argument::any())
      ->willReturn(NULL);

    $this->cacheBackend = new NullBackend('rules');
    $rules_directory = __DIR__ . '/../../../..';
    $this->namespaces = new \ArrayObject([
      'Drupal\\rules' => $rules_directory . '/src',
      'Drupal\\rules_test' => $rules_directory . '/tests/modules/rules_test/src',
      'Drupal\\Core\\TypedData' => $this->root . '/core/lib/Drupal/Core/TypedData',
      'Drupal\\Core\\Validation' => $this->root . '/core/lib/Drupal/Core/Validation',
    ]);

    $this->actionManager = new RulesActionManager($this->namespaces, $this->cacheBackend, $this->moduleHandler->reveal());
    $this->conditionManager = new ConditionManager($this->namespaces, $this->cacheBackend, $this->moduleHandler->reveal());

    $uuid_service = new Php();
    $this->rulesExpressionManager = new ExpressionManager($this->namespaces, $this->moduleHandler->reveal(), $uuid_service);

    $this->classResolver = $this->prophesize(ClassResolverInterface::class);

    $this->typedDataManager = new TypedDataManager(
      $this->namespaces,
      $this->cacheBackend,
      $this->moduleHandler->reveal(),
      $this->classResolver->reveal()
    );
    $this->rulesDataProcessorManager = new DataProcessorManager($this->namespaces, $this->moduleHandler->reveal());

    $this->aliasManager = $this->prophesize(AliasManagerInterface::class);

    // Keep the deprecated entity manager around because it is still used in a
    // few places.
    $this->entityManager = $this->prophesize(EntityManagerInterface::class);

    $this->entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $this->entityTypeManager->getDefinitions()->willReturn([]);

    // Setup a rules_component storage mock which returns nothing by default.
    $storage = $this->prophesize(ConfigEntityStorageInterface::class);
    $storage->loadMultiple(NULL)->willReturn([]);
    $this->entityTypeManager->getStorage('rules_component')->willReturn($storage->reveal());

    $this->entityFieldManager = $this->prophesize(EntityFieldManagerInterface::class);
    $this->entityFieldManager->getBaseFieldDefinitions()->willReturn([]);

    $this->entityTypeBundleInfo = $this->prophesize(EntityTypeBundleInfoInterface::class);
    $this->entityTypeBundleInfo->getBundleInfo()->willReturn([]);

    $this->dataFetcher = new DataFetcher();

    $this->dataFilterManager = new DataFilterManager($this->namespaces, $this->moduleHandler->reveal());
    $this->placeholderResolver = new PlaceholderResolver($this->dataFetcher, $this->dataFilterManager);

    $container->set('entity.manager', $this->entityManager->reveal());
    $container->set('entity_type.manager', $this->entityTypeManager->reveal());
    $container->set('entity_field.manager', $this->entityFieldManager->reveal());
    $container->set('entity_type.bundle.info', $this->entityTypeBundleInfo->reveal());
    $container->set('context.repository', new LazyContextRepository($container, []));
    $container->set('path.alias_manager', $this->aliasManager->reveal());
    $container->set('plugin.manager.rules_action', $this->actionManager);
    $container->set('plugin.manager.condition', $this->conditionManager);
    $container->set('plugin.manager.rules_expression', $this->rulesExpressionManager);
    $container->set('plugin.manager.rules_data_processor', $this->rulesDataProcessorManager);
    $container->set('typed_data_manager', $this->typedDataManager);
    $container->set('string_translation', $this->getStringTranslationStub());
    $container->set('uuid', $uuid_service);
    $container->set('typed_data.data_fetcher', $this->dataFetcher);
    $container->set('typed_data.placeholder_resolver', $this->placeholderResolver);

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
   * @param mixed $value
   *   The value to set.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface
   *   The created object.
   */
  protected function getTypedData($data_type, $value) {
    $definition = $this->typedDataManager->createDataDefinition($data_type);
    $data = $this->typedDataManager->create($definition);
    $data->setValue($value);
    return $data;
  }

  /**
   * Helper method to mock irrelevant cache methods on entities.
   *
   * @param string $interface
   *   The interface that should be mocked, example: EntityInterface::class.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Prophecy\Prophecy\ProphecyInterface
   *   The mocked entity.
   */
  protected function prophesizeEntity($interface) {
    $entity = $this->prophesize($interface);
    // Cache methods are irrelevant for the tests but might be called.
    $entity->getCacheContexts()->willReturn([]);
    $entity->getCacheTags()->willReturn([]);
    $entity->getCacheMaxAge()->willReturn(0);
    return $entity;
  }

}
