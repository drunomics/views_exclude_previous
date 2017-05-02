<?php

namespace Drupal\views_exclude_previous\Plugin\views\argument_default;

use Drupal\Core\Entity\EntityType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use drunomics\ServiceUtils\Core\Entity\EntityTypeManagerTrait;
use Drupal\views_exclude_previous\EntityRenderHistoryTrait;

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
  use EntityTypeManagerTrait;

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
