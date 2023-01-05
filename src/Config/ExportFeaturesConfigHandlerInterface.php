<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Config;

/**
 * Defines the interface for handling export features settings configuration.
 */
interface ExportFeaturesConfigHandlerInterface {

  /**
   * Gets the converter URL if defined.
   *
   * @return string|null
   *   The URL defaults to null.
   */
  public function getConverterUrl(): ?string;

  /**
   * Checks if the converter URL was defined.
   *
   * @return bool
   *   True if URL was defined, false otherwise.
   */
  public function hasConverterUrl(): bool;

  /**
   * Gets the converter options.
   *
   * It is filtering the empty values
   * in order to use the plugin defaults.
   *
   * @return array
   *   The converter options.
   */
  public function getConverterOptions(): array;

}
