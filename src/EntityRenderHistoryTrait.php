<?php

namespace Drupal\views_exclude_previous;

/**
 * Allows setter injection and simple usage of the service.
 */
trait EntityRenderHistoryTrait {

  /**
   * The entity type manager.
   *
   * @var \Drupal\views_exclude_previous\EntityRenderHistory
   */
  protected $entityRenderHistory;

  /**
   * Sets the entity type.
   *
   * @param \Drupal\views_exclude_previous\EntityRenderHistory $entityRenderHistory
   *   The entity render history.
   *
   * @return $this
   */
  public function setEntityRenderHistory(EntityRenderHistory $entityRenderHistory) {
    $this->entityRenderHistory = $entityRenderHistory;
    return $this;
  }

  /**
   * Gets the entity type manager.
   *
   * @return \Drupal\views_exclude_previous\EntityRenderHistory
   *   The entity type manager.
   */
  public function getEntityRenderHistory() {
    if (empty($this->entityRenderHistory)) {
      $this->entityRenderHistory = \Drupal::service('views_exclude_previous.render_history');
    }
    return $this->entityRenderHistory;
  }

}
