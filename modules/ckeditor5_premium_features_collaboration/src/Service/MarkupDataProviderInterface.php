<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Service;

/**
 * Provides the interface for the HTML markup data handlers.
 */
interface MarkupDataProviderInterface {

  public const TAG_SUGGESTION = 'suggestion-start';

  public const TAG_COMMENT = 'comment-start';

}
