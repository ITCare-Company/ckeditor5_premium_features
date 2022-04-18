<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class AnnotationSidebar {

  /**
   * Process the text_format form element.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   * @param array $complete_form
   *   The form structure.
   *
   * @return array
   *   The element data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function process(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    $sidebar_mode = \Drupal::service('config.factory')
      ->getEditable('ckeditor5_premium_features_collaboration.settings')
      ->get('sidebar') ?? 'auto';

    $sidebar['ck_sidebar_type'] = [
      '#type' => 'hidden',
      '#value' => $sidebar_mode,
    ];
    $sidebar['ck_sidebar'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['ck-sidebar-wrapper', $sidebar_mode],
        'id' => [
          $element['#id'] . '-value-ck-sidebar',
        ],
      ],
    ];
    // TODO: Change to something.
    $class_wrapper = $element['#id'] . '-value-ck-sidebar-wrapper';
    $sidebar_html = \Drupal::service('renderer')->render($sidebar);
    $element['value']['#prefix'] = "<div class='ck-editor-sidebar-wrapper $class_wrapper'>";
    $element['value']['#suffix'] = $sidebar_html . '</div>';
    $element['#attached']['drupalSettings']['ckeditor5SidebarMode'] = $sidebar_mode;

    return $element;
  }

}
