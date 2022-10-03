<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Element;

use Drupal\Core\Form\FormStateInterface;

/**
 * Add sidebar view mode, when comments or track changes plugin is on.
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
    /** @var \Drupal\ckeditor5_premium_features_collaboration\Utility\CollaborationSettings $collaboration_settings */
    $collaboration_settings = \Drupal::service('ckeditor5_premium_features_collaboration.collaboration_settings');
    $sidebar_mode = $collaboration_settings->getAnnotationSidebarType();

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

    $class_wrapper = $element['#id'] . '-value-ck-sidebar-wrapper';
    $sidebar_html = \Drupal::service('renderer')->render($sidebar);
    $element['value']['#prefix'] = "<div class='ck-editor-sidebar-wrapper $class_wrapper'>";
    $element['value']['#suffix'] = $sidebar_html . '</div>';
    $element['#attached']['drupalSettings']['ckeditor5SidebarMode'] = $sidebar_mode;

    return $element;
  }

}
