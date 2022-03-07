<?php

namespace Drupal\ckeditor5_premium_features\Generator;

/**
 * Defines the interface for the token generators.
 */
interface FileNameGeneratorInterface {

  /**
   * Generates the token.
   *
   * @return string
   *   The token.
   */
  public function generate(): string;

}
