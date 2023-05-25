<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_productivity_pack\Element;

use Drupal\ckeditor5_premium_features\Element\Ckeditor5TextFormatInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Text Format utility class for handling the collaboration data.
 */
class TextFormat implements Ckeditor5TextFormatInterface {

  /**
   * @inheritDoc
   */
  public static function onCompleteFormSubmit(array &$form, FormStateInterface $form_state): void {

  }

  /**
   * @inheritDoc
   */
  public static function onValidateForm(array &$form, FormStateInterface $form_state): void {

  }

  /**
   * @inheritDoc
   */
  public function processElement(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    return $element;
  }

  /**
   * @inheritDoc
   */
  public static function process(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    // Creates a document outline container with id specific for given field item.
    $document_outline_container = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['document-outline-container', 'collapsed'],
        'id' => [
          $element["#attributes"]["data-drupal-selector"] . '-value-ck-document-outline',
        ],
      ],
    ];

    $container_html = \Drupal::service('renderer')->render($document_outline_container);
    $element['value']['#prefix'] .= $container_html;

    // Add a wrapper class to the field tag.
    $parents = array_slice($element["#array_parents"],0, -2);
    $parent = NestedArray::getValue($complete_form, $parents);
    $parent["#attributes"]["class"][] = 'ck-document-outline-wrapper';
    NestedArray::setValue($complete_form, $parents, $parent);

    return $element;
  }

  /**
   * @inheritDoc
   */
  public function completeFormSubmit(array &$form, FormStateInterface $form_state): void {

  }

  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {

  }

}
