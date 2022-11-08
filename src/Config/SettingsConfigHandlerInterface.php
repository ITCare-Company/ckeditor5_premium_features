<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Config;

/**
 * Defines the settings config interface.
 */
interface SettingsConfigHandlerInterface {

  const DLL_PATH_VERSION_TOKEN = 'VERSION_TOKEN';
  const ORGANISATION_ID_TOKEN = 'ORGANIZATION_ID';
  const ENVIRONMENT_ID_TOKEN = 'ENVIRONMENT_ID';

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
   * Getter for the web socket url.
   *
   * @return string|null
   *   The web socket url if defined, null otherwise.
   */
  public function getWebSocketUrl(): ?string;

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

  /**
   * Gets the API base path.
   */
  public function getApiUrl(): string;

  /**
   * Gets the API authorisation Key.
   */
  public function getApiKey(): ?string;

  /**
   * Gets the default DLL location if it was not overridden in the config.
   *
   * @return string
   *   The URL of the DLL location.
   */
  public function getDefaultDllLocation(): string;

  /**
   * Gets the default Web Socket URL.
   */
  public function getDefaultWebSocketUrl(): string;

  /**
   * Gets the default Api URL.
   */
  public function getDefaultApiUrl(): string;

  /**
   * Gets the DLLs version.
   *
   * @return string
   *   The DLLs version.
   */
  public function getDllVersion(): string;

  /**
   * Gets the organization ID based on the configuration values.
   *
   * @return string|null
   *   The URL of the DLL location.
   */
  public function getOrganizationId(): ?string;

}
