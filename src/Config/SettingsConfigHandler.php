<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Config;

use Drupal\ckeditor5_premium_features\Enum\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Url;

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

  /**
   * Getter for the development token url.
   *
   * @return string|null
   *   The development token url if defined, null otherwise.
   */
  public function getDevelopmentTokenUrl(): ?string {
    return $this->config->get('dev_token_url');
  }

  /**
   * Gets the token URL based on the configuration values.
   *
   * @return string
   *   The token URL.
   */
  public function getTokenUrl(): string {
    if ($token_url = $this->getDevelopmentTokenUrl()) {
      return $token_url;
    }

    if ($this->getAccessKey() && $this->getEnvironmentId()) {
      return Url::fromRoute('ckeditor5_premium_features.endpoint.jwt_token')
        ->toString(TRUE)
        ->getGeneratedUrl();
    }

    // The empty string allows to use the evaluation version note.
    return '';
  }

}
