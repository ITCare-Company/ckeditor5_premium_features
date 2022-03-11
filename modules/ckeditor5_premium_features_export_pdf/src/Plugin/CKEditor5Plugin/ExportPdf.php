<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_pdf\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5_premium_features\Generator\FileNameGeneratorInterface;
use Drupal\ckeditor5_premium_features_export_pdf\Config\SettingsConfigHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 "Export to Pdf" plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ExportPdf extends CKEditor5PluginDefault implements ContainerFactoryPluginInterface {

  /**
   * Creates the cloud service plugin instance.
   *
   * @param \Drupal\ckeditor5_premium_features_export_pdf\Config\SettingsConfigHandlerInterface $settingsConfigHandler
   *   The settings configuration handler.
   * @param \Drupal\ckeditor5_premium_features\Generator\FileNameGeneratorInterface $fileNameGenerator
   *   The name file generator service.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   */
  public function __construct(
    protected SettingsConfigHandlerInterface $settingsConfigHandler,
    protected FileNameGeneratorInterface $fileNameGenerator,
    ...$parent_arguments
  ) {
    parent::__construct(...$parent_arguments);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, ...$parent_arguments): static {
    return new static(
      $container->get('ckeditor5_premium_features_export_pdf.config_handler.settings'),
      $container->get('ckeditor5_premium_features.file_name_generator'),
      ...$parent_arguments
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    if ($this->settingsConfigHandler->hasConverterUrl()) {
      $static_plugin_config['exportPdf']['converterUrl'] = $this->settingsConfigHandler->getConverterUrl();
    }
    $static_plugin_config['exportPdf']['converterOptions'] = $this->settingsConfigHandler->getConverterOptions();
    $static_plugin_config['exportPdf']['fileName'] = $this->fileNameGenerator->generateFromRequest();

    return $static_plugin_config;
  }

}
