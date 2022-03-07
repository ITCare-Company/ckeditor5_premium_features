<?php

namespace Drupal\ckeditor5_premium_features\Generator;

/**
 * Defines the interface for the name file generator.
 */
interface FileNameGeneratorInterface {

  /**
   * Generates the file name.
   *
   * @return string
   *   File name.
   */
  public function generate(): string;

}
