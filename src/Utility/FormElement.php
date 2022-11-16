<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Utility;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides the common form elements that may be reused among the features.
 */
class FormElement {

  /**
   * Adds the format select field to the element.
   *
   * @param array $element
   *   The form or form element to which the format
   *   should be added.
   * @param array $options
   *   The additional options to merged into element.
   */
  public static function format(array &$element, array $options = []): void {
    $element['format'] = $options + [
      '#type' => 'select',
      '#title' => new TranslatableMarkup('Page format'),
      '#options' => [
        'Letter' => new TranslatableMarkup('Letter'),
        'Legal' => new TranslatableMarkup('Legal'),
        'Tabloid' => new TranslatableMarkup('Tabloid'),
        'Statement' => new TranslatableMarkup('Statement'),
        'Executive' => new TranslatableMarkup('Executive'),
        'A3' => new TranslatableMarkup('A3'),
        'A4' => new TranslatableMarkup('A4'),
        'A5' => new TranslatableMarkup('A5'),
        'A6' => new TranslatableMarkup('A6'),
        'B4' => new TranslatableMarkup('B4'),
        'B5' => new TranslatableMarkup('B5'),
      ],
    ];
  }

  /**
   * Adds the page orientation select field to the element.
   *
   * @param array $element
   *   The form or form element to which the page orientation
   *   should be added.
   * @param array $options
   *   The additional options to merged into element.
   */
  public static function pageOrientation(array &$element, array $options, ): void {
    $element['page_orientation'] = $options + [
      '#type' => 'select',
      '#title' => new TranslatableMarkup('Page orientation'),
      '#options' => [
        'portrait' => new TranslatableMarkup('Portrait'),
        'landscape' => new TranslatableMarkup('Landscape'),
      ],
    ];
  }

  /**
   * Adds the footer or header to the element.
   *
   * @param array $element
   *   The form or form element to which the format
   *   should be added.
   * @param string $type
   *   The type: footer or header.
   * @param array $options
   *   The additional options to merged into element.
   */
  public static function headingFooter(array &$element, string $type = 'header', array $options = [], $items_length = 1): void {
    $actions = [
      '#type' => 'container',
    ];

    $selector = $type . '-items-wrapper';
    $fieldset = [
      '#type' => 'fieldset',
      '#title' => new TranslatableMarkup(ucfirst($type) . 's'),
      '#tree' => TRUE,
      '#id' => $selector,
    ];

    for ($index = 0; $index < $items_length; $index++) {
      $fieldset[$index]['html'] = [
        '#type' => 'textarea',
        '#title' => 'HTML',
        '#default_value' => $options[$index]['html'] ?? NULL,
        '#prefix' => $index > 0 ? '<br />' : '',
      ];

      $fieldset[$index]['css'] = [
        '#type' => 'textarea',
        '#title' => 'CSS',
        '#default_value' => $options[$index]['css'] ?? NULL
      ];

      $fieldset[$index]['type'] = [
        '#type' => 'select',
        '#title' => new TranslatableMarkup('Type'),
        '#default_value' => $options[$index]['type'] ?? NULL,
        '#options' => [
          'default' => new TranslatableMarkup('Default'),
          'even' => new TranslatableMarkup('Even'),
          'odd' => new TranslatableMarkup('Odd'),
          'first' => new TranslatableMarkup('First'),
        ],
      ];
    }

    $actions['add_' . $type] = [
      '#type' => 'submit',
      '#value' => new TranslatableMarkup('Add one more ' . $type),
      '#submit' => [
        [
          static::class,
          $type . 'AddOne',
        ],
      ],
      '#ajax' => [
        'callback' => [
          static::class,
          $type . 'AddMoreWordCallback',
        ],
        'wrapper' => $selector,
      ],
    ];

    if ($items_length > 1) {
      $actions['remove_' . $type] = [
        '#type' => 'submit',
        '#value' => new TranslatableMarkup('Remove one ' . $type),
        '#submit' => [
          [
            static::class,
            $type . 'RemoveCallback',
          ],
        ],
        '#ajax' => [
          'callback' => [
            static::class,
            $type . 'AddMoreWordCallback',
          ],
          'wrapper' => $selector,
        ],
      ];
    }
    $fieldset['actions'] = $actions;
    $element[$type] = $fieldset;
  }

  /**
   * Sets form element placeholders if corresponding key is found in the placeholders array.
   *
   * @param array $form
   *   Form to be processed.
   * @param array $placeholders
   *   Array containing placeholders to be used.
   */
  static function setPlaceholders(array &$form, array $placeholders): void {
    foreach ($form as $name => &$item) {
      if (!is_array($item)) {
        continue;
      }
      if (array_key_exists('#default_value', $item)) {
        if (isset($placeholders[$name])) {
          $item['#attributes']['placeholder'] = $placeholders[$name];
        }
      }
      else {
        static::setPlaceholders($item, $placeholders);
      }
    }
  }

  /**
   * Walks through the form and disables all fields and buttons.
   *
   * @param $form
   *   Form to be processed.
   */
  static function disableFormFields(&$form): void {
    foreach ($form as $key => &$item) {
      if (!is_array($item) || $key == 'override_global') {
        continue;
      }
      if (array_key_exists('#default_value', $item) || isset($item['#type']) && $item['#type'] == 'submit') {
        $item['#disabled'] = TRUE;
      }
      else {
        static::disableFormFields($item);
      }
    }
  }

  /**
   * Callback for export to Word header buttons (add/remove).
   */
  static function headerAddMoreWordCallback(array &$form, FormStateInterface $form_state): ?array {
    $settings_element = $form['editor']['settings']['subform']['plugins']['ckeditor5_premium_features_export_word__export_word'] ?? $form;

    return $settings_element['converter_options']['header'] ?? NULL;
  }

  /**
   * Callback for export to Word footer buttons (add/remove).
   */
  static function footerAddMoreWordCallback(array &$form, FormStateInterface $form_state): ?array {
    $settings_element = $form['editor']['settings']['subform']['plugins']['ckeditor5_premium_features_export_word__export_word'] ?? $form;

    return $settings_element['converter_options']['footer'] ?? NULL;
  }

  /**
   * Submit handler for the header "add-one-more" button.
   */
  static function headerAddOne(array &$form, FormStateInterface $form_state): void {
    static::modifyItemCounter($form_state, 'num_headers', 1);
  }

  /**
   * Submit handler for the footer "add-one-more" button.
   */
  static function footerAddOne(array &$form, FormStateInterface $form_state) : void {
    static::modifyItemCounter($form_state, 'num_footers', 1);
  }

  /**
   * Submit handler for the header "remove-one" button.
   */
  static function headerRemoveCallback(array &$form, FormStateInterface $form_state): void {
    static::modifyItemCounter($form_state, 'num_headers');
  }

  /**
   * Submit handler for the footer "remove-one" button.
   */
  static function footerRemoveCallback(array &$form, FormStateInterface $form_state): void {
    static::modifyItemCounter($form_state, 'num_footers');
  }

  /**
   * Increments or decrements the items max counter and causes a rebuild.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param string$counter_name
   *   Name of property to modify.
   * @param int $modifier
   *   Value to modify the counter.
   *
   * @return void
   */
  static function modifyItemCounter(FormStateInterface $form_state, string $counter_name, int $modifier = -1): void {
    $name_field = $form_state->get($counter_name);
    if ($modifier > 0 || $name_field > abs($modifier)) {
      $remove_button = $name_field + $modifier;
      $form_state->set($counter_name, $remove_button);
    }

    $form_state->setRebuild();
  }

}
