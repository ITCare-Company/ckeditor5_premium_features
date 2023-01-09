<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_pdf\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5_premium_features\Plugin\CKEditor5Plugin\ExportBase;

/**
 * CKEditor 5 "Export to Pdf" plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ExportPdf extends ExportBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'converter_url' => NULL,
      'converter_options' => [
        'custom_css' => NULL,
        'format' => NULL,
        'margin_top' => [
          'value' => NULL,
          'units' => NULL,
        ],
        'margin_bottom' => [
          'value' => NULL,
          'units' => NULL,
        ],
        'margin_left' => [
          'value' => NULL,
          'units' => NULL,
        ],
        'margin_right' => [
          'value' => NULL,
          'units' => NULL,
        ],
        'page_orientation' => NULL,
        'header_html' => NULL,
        'footer_html' => NULL,
        'header_and_footer_css' => NULL,
      ],
    ];
  }

}
