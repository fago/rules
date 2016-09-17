<?php

namespace Drupal\rules\ComponentResolver;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rules\Engine\ExpressionManagerInterface;
use Drupal\rules\Engine\RulesComponent;
use Drupal\rules\Engine\RulesComponentResolverInterface;

/**
 * Resolves components that hold all reaction rules for a given event.
 */
class EventComponentResolver implements RulesComponentResolverInterface {

  /**
   * The rules component entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * The Rules expression manager.
   *
   * @var \Drupal\rules\Engine\ExpressionManagerInterface
   */
  protected $expressionManager;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\rules\Engine\ExpressionManagerInterface $expression_manager
   *   The rules expression plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ExpressionManagerInterface $expression_manager) {
    $this->entityStorage = $entity_type_manager->getStorage('rules_reaction_rule');
    $this->expressionManager = $expression_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $event_ids) {
    // @todo: Improve this by adding a custom expression plugin that clones
    // the state after each rule, such that added variables added by one rule
    // are not interfering with the variables of another rule.
    $results = [];
    foreach ($event_ids as $event_id) {
      $action_set = $this->expressionManager->createActionSet();
      // @todo Only load active reaction rules here.
      $configs = $this->entityStorage->loadByProperties(['events.*.event_name' => $event_id]);
      foreach ($configs as $config) {
        $action_set->addExpressionObject($config->getExpression());
      }
      $results[$event_id] = RulesComponent::create($action_set);
    }
    return $results;
  }

}
