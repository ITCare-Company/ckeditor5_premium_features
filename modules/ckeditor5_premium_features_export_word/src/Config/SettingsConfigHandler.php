<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_word\Config;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Provides handler for the "Export to Word" module base settings configuration.
 */
class SettingsConfigHandler implements SettingsConfigHandlerInterface {

  public const CONFIG_NAME = 'ckeditor5_premium_features_export_word.settings';

  /**
   * The configuration object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * Constructs the handler.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(protected ConfigFactoryInterface $configFactory) {
    $this->config = $this->configFactory->get(static::CONFIG_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public function getConverterUrl(): ?string {
    return $this->config->get('converter_url');
  }

  /**
   * {@inheritdoc}
   */
  public function hasConverterUrl(): bool {
    return (bool) $this->getConverterUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getConverterOptions(): array {
    $options = $this->config->get('converter_options') ?? [];

    return array_filter($options);
  }

}
