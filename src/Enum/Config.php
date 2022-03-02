<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Enum;

enum Config {

  case SETTINGS;

  /**
   * Resolves the case to the config name.
   *
   * @return string
   *   The related config name.
   */
  public function name(): string {
    return match($this) {
      Config::SETTINGS => 'ckeditor5_premium_features.settings',
    };
  }

}
