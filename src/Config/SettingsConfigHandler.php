<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Config;

use Drupal\ckeditor5_premium_features\Enum\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Provides the utility service for handling the stored settings configuration.
 */
class SettingsConfigHandler implements SettingsConfigHandlerInterface {

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
    $this->config = $this->configFactory->get(Config::SETTINGS->name());
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessKey(): ?string {
    return $this->config->get('access_key');
  }

  /**
   * {@inheritdoc}
   */
  public function getEnvironmentId(): ?string {
    return $this->config->get('env');
  }

}
