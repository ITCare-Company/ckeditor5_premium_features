<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Entity;

/**
 * Defines the Suggestion entity storage methods.
 */
interface SuggestionStorageInterface {

  /**
   * Creates the CKEDitor5 Suggestion entity.
   *
   * @param array $raw_data
   *   The raw data to be used in the creation.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface
   *   The created entity.
   */
  public function add(array $raw_data): SuggestionInterface;

  /**
   * Updates the CKEDitor5 Suggestion entity.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $suggestion
   *   The suggestion entity.
   * @param array $raw_data
   *   The raw data to be updated.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface
   *   The created entity.
   */
  public function update(SuggestionInterface $suggestion, array $raw_data): SuggestionInterface;

}
