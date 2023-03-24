<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_pdf\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5_premium_features\Plugin\CKEditor5Plugin\ExportBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $config = $plugin_definition->toArray()['drupal']['premium_features'];

    return new static(
      $config['plugin'],
      $config['settings_form'],
      $config['file_extension'],
      $container->get('config.factory'),
      $container->get('ckeditor5_premium_features_export_pdf.config_handler.export_settings')->setConfig($config['configuration']),
      $container->get('ckeditor5_premium_features.file_name_generator'),
      $container->get('ckeditor5_premium_features.css_style_provider'),
      $container->get('file_system'),
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'converter_url' => NULL,
      'converter_options' => [
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
        'custom_css' => NULL,
        'header_html' => NULL,
        'footer_html' => NULL,
        'header_and_footer_css' => NULL,
      ],
    ];
  }

}
