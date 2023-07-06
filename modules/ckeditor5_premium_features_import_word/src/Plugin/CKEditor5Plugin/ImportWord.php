<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_import_word\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
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
   * @param ImportWordConfigHandlerInterface $configHandler
   *   The settings configuration handler.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   */
  public function __construct(
    protected ImportWordConfigHandlerInterface $configHandler,
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

    $static_plugin_config['importWord']['defaultStyles'] = $this->configHandler->isWordStylesEnabled();

    return $static_plugin_config;
  }

}
