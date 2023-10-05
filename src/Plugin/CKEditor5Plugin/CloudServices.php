<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 CloudServices plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class CloudServices extends CKEditor5PluginDefault implements ContainerFactoryPluginInterface {

  /**
   * Creates the cloud service plugin instance.
   *
   * @param \Drupal\ckeditor5_premium_features\Config\SettingsConfigHandlerInterface $settingsConfigHandler
   *   The settings configuration handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   */
  public function __construct(
    protected SettingsConfigHandlerInterface $settingsConfigHandler,
    protected ConfigFactoryInterface $configFactory,
    ...$parent_arguments
  ) {
    parent::__construct(...$parent_arguments);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, ...$parent_arguments): static {
    return new static(
      $container->get('ckeditor5_premium_features.config_handler.settings'),
      $container->get('config.factory'),
      ...$parent_arguments
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $filterFormatId = $editor->getFilterFormat()->id();
    $static_plugin_config['cloudServices']['tokenUrl'] = $this->settingsConfigHandler->getTokenUrl($filterFormatId);
    $static_plugin_config['cloudServices']['webSocketUrl'] = $this->settingsConfigHandler->getWebSocketUrl();

    $config = $this->configFactory->get('ckeditor5_premium_features_realtime_collaboration.settings');
    $bundles = $config->get('editor_bundles') ?? [];
    $bundleVersion = $bundles[$editor->id()] ?? '';
    if ($bundleVersion) {
      $static_plugin_config['cloudServices']['bundleVersion'] = $bundleVersion;
    }

    $static_plugin_config['comments']['editorConfig']['extraPlugins'] = [];

    return $static_plugin_config;
  }

}
