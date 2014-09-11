<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Unit\RulesIntegrationTestBase.
 */

namespace Drupal\Tests\rules\Unit;

use Drupal\Core\Action\ActionManager;
use Drupal\Core\Cache\NullBackend;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\Plugin\DataType\Any;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\rules\Plugin\RulesDataProcessorManager;
use Drupal\rules\Plugin\RulesExpressionPluginManager;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\Definition;

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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $container = new ContainerBuilder();
    // Register plugin managers used by Rules, but mock some unwanted
    // dependencies requiring more stuff to loaded.
    $module_handler = $this->getMockBuilder('Drupal\Core\Extension\ModuleHandlerInterface')
      ->disableOriginalConstructor()
      ->getMock();
    // Set all the modules as being existant.
    $module_handler->expects($this->any())
      ->method('moduleExists')
      ->will($this->returnValueMap([
        [ 'rules', TRUE ],
        [ 'rules_test', TRUE],
      ]));

    $cache_backend = new NullBackend('rules');
    $rules_directory = __DIR__ . '/../../..';

    $namespaces = new \ArrayObject(array(
      'Drupal\\rules' => $rules_directory . '/src',
      'Drupal\\rules_test' => $rules_directory . '/tests/modules/rules_test/src',
      'Drupal\\Core\\TypedData' => DRUPAL_ROOT . '/core/lib/Drupal/Core/TypedData',
      'Drupal\\Core\\Validation' => DRUPAL_ROOT . '/core/lib/Drupal/Core/Validation',
    ));

    $this->actionManager = new ActionManager($namespaces, $cache_backend, $module_handler);
    $this->conditionManager = new ConditionManager($namespaces, $cache_backend, $module_handler);
    $this->rulesExpressionManager = new RulesExpressionPluginManager($namespaces, $module_handler);
    $this->typedDataManager = new TypedDataManager($namespaces, $cache_backend, $module_handler);
    $this->rulesDataProcessorManager = new RulesDataProcessorManager($namespaces, $module_handler);

    $container->set('plugin.manager.action', $this->actionManager);
    $container->set('plugin.manager.condition', $this->conditionManager);
    $container->set('plugin.manager.rules_expression', $this->rulesExpressionManager);
    $container->set('plugin.manager.rules_data_processor', $this->rulesExpressionManager);
    $container->set('typed_data_manager', $this->typedDataManager);
    $container->set('string_translation', $this->getStringTranslationStub());

    \Drupal::setContainer($container);
    $this->container = $container;
  }
}
