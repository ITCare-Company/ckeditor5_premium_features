<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Utility;

use Drupal\Component\Utility\NestedArray;
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
  public static function pageOrientation(array &$element, array $options = []): void {
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
  public static function headingFooter(array &$element, string $type = 'heading', array $options = []): void {
    $fieldset = [
      '#type' => 'fieldset',
      '#title' => new TranslatableMarkup(ucfirst($type)),
      '#tree' => TRUE,
    ];
    $items_length = 1;
    for ($index = 0; $index < $items_length; $index++) {
      $fieldset[$index]['html'] = [
        '#type' => 'textarea',
        '#title' => 'HTML',
      ];

      $fieldset[$index]['css'] = [
        '#type' => 'textarea',
        '#title' => 'CSS',
      ];

      $fieldset[$index]['type'] = [
        '#type' => 'select',
        '#title' => new TranslatableMarkup('Type'),
        '#options' => [
          'default' => new TranslatableMarkup('Default'),
          'even' => new TranslatableMarkup('Even'),
          'odd' => new TranslatableMarkup('Odd'),
          'first' => new TranslatableMarkup('First'),
        ],
      ];
    }

    $element[$type] = NestedArray::mergeDeepArray([$fieldset, $options], TRUE);
  }

}
