<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_word\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5_premium_features_export_word\Config\SettingsConfigHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 "Export to Word" plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ExportWord extends CKEditor5PluginDefault implements ContainerFactoryPluginInterface {

  /**
   * Creates the cloud service plugin instance.
   *
   * @param \Drupal\ckeditor5_premium_features_export_word\Config\SettingsConfigHandlerInterface $settingsConfigHandler
   *   The settings configuration handler.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   */
  public function __construct(
    protected SettingsConfigHandlerInterface $settingsConfigHandler,
    ...$parent_arguments
  ) {
    parent::__construct(...$parent_arguments);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, ...$parent_arguments): static {
    return new static(
      $container->get('ckeditor5_premium_features_export_word.config_handler.settings'),
      ...$parent_arguments
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    if ($this->settingsConfigHandler->hasConverterUrl()) {
      $static_plugin_config['exportWord']['converterUrl'] = $this->settingsConfigHandler->getConverterUrl();
    }
    $static_plugin_config['exportWord']['converterOptions'] = $this->settingsConfigHandler->getConverterOptions();

    return $static_plugin_config;
  }

}
