<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Utility;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides the common form elements that may be reused among the features.
 */
class FormElement {

  /**
   * Adds the format select field to the element.
   *
   * @param array $element
   *   The form or form elemen to which the format
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

}
