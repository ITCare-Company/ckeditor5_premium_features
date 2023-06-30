<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Element;

use Drupal\ckeditor5_premium_features\Utility\CommonCollaborationSettingsInterface;

/**
 * Add sidebar view mode, when comments or track changes plugin is on.
 */
class AnnotationSidebar {

  /**
   * Process the text_format form element.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\ckeditor5_premium_features\Utility\CommonCollaborationSettingsInterface $collaboration_settings
   *   Settings service.
   *
   * @return array
   *   The element data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function process(array &$element, CommonCollaborationSettingsInterface $collaboration_settings): array {
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
