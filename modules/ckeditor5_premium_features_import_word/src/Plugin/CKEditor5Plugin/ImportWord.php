<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_import_word\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5_premium_features\Utility\LibraryVersionChecker;
use Drupal\ckeditor5_premium_features_import_word\Config\ImportWordConfigHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 "Import from Word" plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ImportWord extends CKEditor5PluginDefault implements ContainerFactoryPluginInterface {

  /**
   * Creates the plugin instance.
   *
   * @param \Drupal\ckeditor5_premium_features_import_word\Config\ImportWordConfigHandlerInterface $configHandler
   *   The settings configuration handler.
   * @param \Drupal\ckeditor5_premium_features\Utility\LibraryVersionChecker $libraryVersionChecker
   *   CKEditor 5 library checker.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   */
  public function __construct(
    protected ImportWordConfigHandlerInterface $configHandler,
    protected LibraryVersionChecker $libraryVersionChecker,
    ...$parent_arguments
  ) {
    parent::__construct(...$parent_arguments);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $container->get('ckeditor5_premium_features_import_word.config_handler.settings'),
      $container->get('ckeditor5_premium_features.core_library_version_checker'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $static_plugin_config = parent::getDynamicPluginConfig($static_plugin_config, $editor);

    if ($this->libraryVersionChecker->isLibraryVersionHigherOrEqual('40.1.0')) {
      $isWordStylesEnabled = $this->configHandler->isWordStylesEnabled();
      $static_plugin_config['importWord']['formatting']['defaults'] = $isWordStylesEnabled ? 'inline' : 'none';
      $static_plugin_config['importWord']['formatting']['resets'] = $isWordStylesEnabled ? 'inline' : 'none';
    }
    else {
      $static_plugin_config['importWord']['defaultStyles'] = $this->configHandler->isWordStylesEnabled();
    }

    return $static_plugin_config;
  }

}
