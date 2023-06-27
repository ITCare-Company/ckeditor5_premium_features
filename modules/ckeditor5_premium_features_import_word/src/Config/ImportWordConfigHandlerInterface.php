<?php

namespace Drupal\ckeditor5_premium_features_import_word\Config;

interface ImportWordConfigHandlerInterface {

  /**
   * Check if Word styles should be preserved on import.
   *
   * @return bool
   */
  public function isWordStylesEnabled(): bool;

}
