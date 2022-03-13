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
        'format' => NULL,
        'margin_top' => NULL,
        'margin_bottom' => NULL,
        'margin_left' => NULL,
        'margin_right' => NULL,
        'page_orientation' => NULL,
        'header_html' => NULL,
        'footer_html' => NULL,
        'header_and_footer_css' => NULL,
      ],
    ];
  }

}
