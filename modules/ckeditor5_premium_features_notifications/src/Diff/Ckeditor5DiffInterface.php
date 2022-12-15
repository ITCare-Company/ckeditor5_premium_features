<?php

namespace Drupal\ckeditor5_premium_features_notifications\Diff;

/**
 * Interface for Ckeditor5 Diff class.
 */
interface Ckeditor5DiffInterface {

  /**
   * Processes two documents to find all changes between old and new document.
   *
   * @param string $oldDocument
   *   String representing previous version of a document.
   * @param string $newDocument
   *   String representing new version of a document.
   *
   * @return string|null
   *   Returns a string with all new content if anything was added to the
   *   new document.
   */
  public function getDiff(string $oldDocument, string $newDocument): ?string;

  /**
   * Returns a wider context presenting added parts with surrounding text.
   */
  public function getDiffAddedContext(): ?string;

  /**
   * Returns a wider context presenting all modified document parts.
   */
  public function getDiffContext(): ?string;

}
