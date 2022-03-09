<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_word\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5_premium_features\Plugin\CKEditor5Plugin\ExportBase;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 "Export to Word" plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ExportWord extends ExportBase {

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
        'header' => [
          [
            'html' => NULL,
            'css' => NULL,
            'type' => NULL,
          ],
        ],
        'footer' => [
          [
            'html' => NULL,
            'css' => NULL,
            'type' => NULL,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $static_plugin_config = parent::getDynamicPluginConfig($static_plugin_config, $editor);

    $options = &$static_plugin_config[$this->getFeaturePlugin()]['converterOptions'];

    foreach (['footer', 'header'] as $item) {
      if (isset($options[$item]) && is_array($options[$item])) {
        $this->cleanUpEmptyHtmlElements($options[$item]);
      }
    }

    return $static_plugin_config;
  }

  /**
   * Removes items that have the emtpty HTML content.
   *
   * @param array $element
   *   The element to be processed.
   */
  private function cleanUpEmptyHtmlElements(array &$element): void {
    foreach ($element as $key => $item) {
      if (empty($item['html'])) {
        unset($element[$key]);
      }
    }
  }

}
