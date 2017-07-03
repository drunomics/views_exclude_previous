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
    $options['view_modes'] = ['default' => []];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    // If we have a value for the first dropdown from $form_state,
    // we use this both as the default value for the first dropdown and also as
    // a parameter to pass to the function that retrieves the options for the
    // second dropdown.
    $selected = $this->getValueFromFormState($form_state, 'entity_type_id') ?
      $this->getValueFromFormState($form_state, 'entity_type_id') :
      $this->options['entity_type_id'];

    $form['entity_type_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#options' => array_map(function (EntityType $entity_type) {
        return $entity_type->getLabel();
      }, $this->getEntityTypeManager()->getDefinitions()),
      '#required' => TRUE,
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => '\Drupal\views_exclude_previous\Plugin\views\argument_default\EntityRenderHistory::ajaxDependentDropdownCallback',
        'wrapper' => 'dropdown-view-mode-replace',
      ],
    ];

    $selected_view_modes = $this->getValueFromFormState($form_state, 'view_modes') ? $this->getValueFromFormState($form_state, 'view_modes') : $this->options['view_modes'];

    $form['view_modes'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('View modes'),
      // The entire enclosing div created here gets replaced when dropdown_first
      // is changed.
      '#prefix' => '<div id="dropdown-view-mode-replace">',
      '#suffix' => '</div>',
      // When the form is rebuilt during ajax processing, the $selected variable
      // will now have the new value and so the options will change.
      '#options' => $this->ajaxGetViewModes($selected),
      '#default_value' => $selected_view_modes,
    ];
  }

  /**
   * Gets a value from this plugin out of the form_state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state, where the value should be looked upon.
   * @param string $key
   *   The key, for which the value should be found.
   *
   * @return mixed
   *   The value.
   */
  protected function getValueFromFormState(FormStateInterface $form_state, $key) {
    return $form_state->getValue('options')['argument_default']['views_exclude_default_render_history'][$key];
  }

  /**
   * Callback to determine what should be displayed as the second dropdown.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return mixed
   *   A renderable object.
   */
  public function ajaxDependentDropdownCallback(array $form, FormStateInterface $form_state) {
    return $form['options']['argument_default']['views_exclude_default_render_history']['view_modes'];
  }

  /**
   * Get all view modes for an entity type.
   *
   * @param string $entity_type_id
   *   The entity type id.
   *
   * @return array
   *   The view modes.
   */
  protected function ajaxGetViewModes($entity_type_id) {
    $options = ['all' => 'All'];
    $view_modes = \Drupal::service('entity_display.repository')->getViewModes($entity_type_id);
    if (!empty($view_modes)) {
      $options['default'] = 'Default';
      foreach ($view_modes as $key => $value) {
        $options[$key] = $value['label'];
      }
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    $ids = $this->getEntityRenderHistory()
      ->getRenderedEntities($this->options['entity_type_id'], $this->options['view_modes']);

    // If no IDs are given, by-pass the filter.
    return implode('+', $ids) ?: 'all';
  }

}
