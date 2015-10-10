<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Condition\EntityIsNew.
 */

namespace Drupal\rules\Plugin\Condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\rules\Core\RulesConditionBase;

/**
 * Provides an 'Entity is new' condition.
 *
 * @Condition(
 *   id = "rules_entity_is_new",
 *   label = @Translation("Entity is new"),
 *   category = @Translation("Entity"),
 *   context = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity for which to evaluate the condition.")
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7?
 */
class EntityIsNew extends RulesConditionBase {

  /**
   * Check if the provided entity is new.
   *
   * @param \Drupal\Core\Entity\EntityInterface $provided_entity
   *   The entity to check if is new.
   *
   * @return bool
   *   TRUE if provided entity is new.
   */
  public function doEvaluate(EntityInterface $provided_entity) {
    return $provided_entity->isNew();
  }

}
