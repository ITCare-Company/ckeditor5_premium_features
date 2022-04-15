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
  public function getLicenseKey(): ?string {
    return $this->config->get('license_key');
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
   * {@inheritdoc}
   */
  public function getDevelopmentTokenUrl(): ?string {
    return $this->config->get('dev_token_url');
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenUrl(): string {
    $type = $this->config->get('auth_type');

    if ($type === 'dev_token' && $token_url = $this->getDevelopmentTokenUrl()) {
      return $token_url;
    }

    if ($type === 'key' && $this->getAccessKey() && $this->getEnvironmentId()) {
      return Url::fromRoute('ckeditor5_premium_features.endpoint.jwt_token')
        ->toString(TRUE)
        ->getGeneratedUrl();
    }

    // The empty string allows to use the evaluation version note.
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getDllLocation(string $file_name = ''): string {
    $base_path = $this->config->get('dll_location') ?: $this->getDefaultDllLocation();

    return $base_path . $file_name;
  }

  /**
   * Gets the default DLL location if it was not overriden in the config.
   *
   * @return string
   *   The URL of the DLL location.
   */
  protected function getDefaultDllLocation(): string {
    $host = Url::fromRoute('<front>')->setAbsolute()->toString();
    // We don't do a DI here, because it will be replaced with the CDN URL.
    $path = \Drupal::moduleHandler()->getModule('ckeditor5_premium_features')->getPath();

    return $host . $path . '/js/';
  }

}
