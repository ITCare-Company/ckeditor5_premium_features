<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Service;

/**
 * Provides the interface for the HTML markup data handlers.
 */
interface MarkupDataProviderInterface {

  /**
   * Gets the users data stored in suggestions.
   *
   * @param string $content
   *   The content containg HTML markup.
   *
   * @return array
   *   The users data.
   */
  public function getSuggestionsUsers(string $content): array;

}
