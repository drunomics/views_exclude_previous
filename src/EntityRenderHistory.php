<?php

namespace Drupal\views_exclude_previous;

use Drupal\Core\Entity\EntityInterface;

/**
 * Keeps the ids of rendered entities during a page request.
 */
class EntityRenderHistory {

  /**
   * List of rendered entity ids, keyed by entity type id.
   *
   * @var array[]
   */
  protected $rendered;

  /**
   * Remembers that the given entity has been rendered.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to add to the history.
   */
  public function add(EntityInterface $entity) {
    $this->rendered[$entity->getEntityTypeId()][$entity->id()] = $entity->id();
  }

  /**
   * Gets all already rendered entities.
   *
   * @param string $entityTypeId
   *   The entity type id.
   *
   * @return int[]
   *   The list of entity ids of the rendered entities.
   */
  public function getRenderedEntities($entityTypeId) {
    return isset($this->rendered[$entityTypeId]) ? $this->rendered[$entityTypeId] : [];
  }

}
