<?php

namespace Drupal\views_exclude_previous\Plugin\views\argument_default;

use Drupal\Core\Entity\EntityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Drupal\views_exclude_previous\EntityRenderHistoryTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default argument plugin to exclude previously rendered entities.
 *
 * @ViewsArgumentDefault(
 *   id = "views_exclude_default_render_history",
 *   title = @Translation("Previously rendered entities")
 * )
 */
class EntityRenderHistory extends ArgumentDefaultPluginBase {

  use EntityRenderHistoryTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Sets the entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   *
   * @return $this
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    return $this;
  }

  /**
   * Gets the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  public function getEntityTypeManager() {
    if (empty($this->entityTypeManager)) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }
    return $this->entityTypeManager;
  }

  /**
   * Sets the currently active request object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The currently active request object.
   *
   * @return $this
   */
  public function setCurrentRequest(Request $request) {
    $this->request = $request;
    return $this;
  }

  /**
   * Retrieves the currently active request object.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The currently active request object.
   */
  public function getCurrentRequest() {
    if (empty($this->request)) {
      $this->request = \Drupal::request();
    }
    return $this->request;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['entity_type_id'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['entity_type_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#options' => array_map(function (EntityType $entity_type) {
        return $entity_type->getLabel();
      }, $this->getEntityTypeManager()->getDefinitions()),
      '#required' => TRUE,
      '#default_value' => $this->options['entity_type_id'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    $ids = $this->getEntityRenderHistory()
      ->getRenderedEntities($this->options['entity_type_id']);

    // Try to get the excluded ids from the current ajax request.
    $excluded_ids = $this->getCurrentRequest()->get('views_exclude_previous_ids');
    if ($excluded_ids) {
      if (!empty($excluded_ids[$this->options['entity_type_id']])) {
        $ids = array_merge($ids, $excluded_ids[$this->options['entity_type_id']]);
      }
    }

    // If no IDs are given, by-pass the filter.
    return implode('+', $ids) ?: 'all';
  }

}
