<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Config;

/**
 * Defines the settings config interface.
 */
interface SettingsConfigHandlerInterface {

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

}
