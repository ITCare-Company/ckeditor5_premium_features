<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Config;

/**
 * Defines the settings config interface.
 */
interface SettingsConfigHandlerInterface {

  /**
   * Getter for the license key.
   *
   * @return string|null
   *   The license key if defined, null otherwise.
   */
  public function getLicenseKey(): ?string;

  /**
   * Getter for the access key.
   *
   * @return string|null
   *   The access key if defined, null otherwise.
   */
  public function getAccessKey(): ?string;

  /**
   * Getter for the env id.
   *
   * @return string|null
   *   The id if defined, null otherwise.
   */
  public function getEnvironmentId(): ?string;

  /**
   * Getter for the development token url.
   *
   * @return string|null
   *   The development token url if defined, null otherwise.
   */
  public function getDevelopmentTokenUrl(): ?string;

  /**
   * Gets the token URL based on the configuration values.
   *
   * @return string
   *   The token URL.
   */
  public function getTokenUrl(): string;

  /**
   * Gets the DLLs location.
   *
   * @return string
   *   The DLLs location.
   */
  public function getDllLocation(string $file_name = ''): string;

}
