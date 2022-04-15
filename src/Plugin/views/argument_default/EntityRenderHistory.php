<?php

namespace Drupal\views_exclude_previous\Plugin\views\argument_default;

use Drupal\Core\Entity\EntityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Drupal\views_exclude_previous\EntityRenderHistoryTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * Constructs a container aware event dispatcher.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
    if (!isset($this->entityTypeManager)) {
      $this->entityTypeManager = $this->container->get('entity_type.manager');
    }
    return $this->entityTypeManager;
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

    // If no IDs are given, by-pass the filter.
    return implode('+', $ids) ?: 'all';
  }

}
