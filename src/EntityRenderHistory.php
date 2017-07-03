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
   * @param string $viewMode
   *   The view mode, the entity was rendered with.
   */
  public function add(EntityInterface $entity, $viewMode) {
    $this->rendered[$entity->getEntityTypeId()]['entities'][$entity->id()] = $entity->id();
    $this->rendered[$entity->getEntityTypeId()]['view_modes'][$entity->id()] = $viewMode;
  }

  /**
   * Gets all already rendered entities, filtered by view modes.
   *
   * @param string $entityTypeId
   *   The entity type id.
   * @param array $viewModes
   *   The view modes.
   *
   * @return int[]
   *   The list of entity ids of the rendered entities.
   */
  public function getRenderedEntities($entityTypeId, array $viewModes) {
    if (!isset($this->rendered[$entityTypeId])) {
      return [];
    }

    $entities = $this->rendered[$entityTypeId]['entities'];

    if (!empty($viewModes) && $viewModes[0] != 'all') {

      $entities = array_filter($entities, function ($entity_id) use ($entityTypeId, $viewModes) {
        if (!empty($this->rendered[$entityTypeId]['view_modes'][$entity_id])
          && in_array($this->rendered[$entityTypeId]['view_modes'][$entity_id], $viewModes)) {
          return TRUE;
        }
        return FALSE;
      });

    }

    return $entities;

  }

}
