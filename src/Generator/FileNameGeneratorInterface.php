<?php

namespace Drupal\ckeditor5_premium_features\Generator;

/**
 * Defines the interface for the name file generator.
 */
interface FileNameGeneratorInterface {

  /**
   * Generate file name based on url/alias.
   *
   * @return string
   *   File name.
   */
  public function generateFromRequest(): string;

  /**
   * Add Extension to filename.
   *
   * @param string $filename
   *   Generated filename.
   * @param string $extension
   *   Extension file to add.
   */
  public function addExtensionFile(string &$filename, string $extension): void;

}
