<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_productivity_pack\Element;

use Drupal\ckeditor5_premium_features\Element\Ckeditor5TextFormatInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class TextFormat implements Ckeditor5TextFormatInterface {

  /**
   * @inheritDoc
   */
  public static function onCompleteFormSubmit(array &$form, FormStateInterface $form_state): void {
    // TODO: Implement onCompleteFormSubmit() method.
  }

  /**
   * @inheritDoc
   */
  public static function onValidateForm(array &$form, FormStateInterface $form_state): void {
    // TODO: Implement onValidateForm() method.
  }

  /**
   * @inheritDoc
   */
  public function processElement(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    // TODO: Implement processElement() method.

    return $element;
  }

  /**
   * @inheritDoc
   */
  public static function process(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    //$sidebar_mode = $collaboration_settings->getAnnotationSidebarType();
//    $sidebar_mode = 'auto';
//
//    $sidebar['ck_sidebar_type'] = [
//      '#type' => 'hidden',
//      '#value' => $sidebar_mode,
//    ];
//    $sidebar['ck_sidebar'] = [
//      '#type' => 'html_tag',
//      '#tag' => 'div',
//      '#attributes' => [
//        'class' => ['ck-sidebar-wrapper', $sidebar_mode],
//        'id' => [
//          $element['#id'] . '-value-ck-sidebar',
//        ],
//      ],
//    ];
//
//    $class_wrapper = $element['#id'] . '-value-ck-sidebar-wrapper';
//    $sidebar_html = \Drupal::service('renderer')->render($sidebar);
//    $element['value']['#prefix'] = "<div class='ck-editor-sidebar-wrapper $class_wrapper'>";
//    $element['value']['#suffix'] = $sidebar_html . '</div>';
//    $element['#attached']['drupalSettings']['ckeditor5SidebarMode'] = $sidebar_mode;

    $element['document_outline_container'] = [
      '#type' => 'container',
      '#weight' => -1,
      '#attributes' => [
        'class' => ['document-outline-container'],
      ],
    ];

    return $element;
  }

  /**
   * @inheritDoc
   */
  public function completeFormSubmit(array &$form, FormStateInterface $form_state): void {
    // TODO: Implement completeFormSubmit() method.
  }

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // TODO: Implement validateForm() method.
  }

}
