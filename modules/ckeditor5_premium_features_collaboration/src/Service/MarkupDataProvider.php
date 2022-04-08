<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Service;

use DOMXPath;
use Drupal\ckeditor5_premium_features_collaboration\EditorElement\CommentItem;
use Drupal\ckeditor5_premium_features_collaboration\EditorElement\SuggestionItem;
use Drupal\Component\Utility\Html;

/**
 * The utility service for handling the data stored in the HTML markup.
 */
class MarkupDataProvider implements MarkupDataProviderInterface {

  /**
   * Gets the list of suggestions IDs.
   *
   * @param string $content
   *   The content containg HTML markup.
   *
   * @return string[]
   *   The list of suggestions IDs.
   */
  public function getSuggestionsIds(string $content): array {
    $suggestions = $this->getTagData(static::TAG_SUGGESTION, $content);

    return array_map(fn ($suggestion) => $suggestion->getSuggestionId(), $suggestions);
  }

  public function getCommentsIds(string $content): array {
    /** @var \Drupal\ckeditor5_premium_features_collaboration\EditorElement\CommentItem[] $comments */
    $comments = $this->getTagData(static::TAG_COMMENT, $content);

    return array_map(fn ($comment) => $comment->getThreadId(), $comments);
  }

  /**
   * Loads the elements from the given tag and markup.
   *
   * @param string $tag
   *   The HTML tag name.
   * @param string $content
   *   The markup string.
   *
   * @return \DOMNodeList|false|mixed
   *   The founded tags.
   */
  protected function loadFromMarkup(string $tag, string $content): mixed {
    $dom = Html::load($content);
    $xpath = new DOMXPath($dom);

    return $xpath->query('//' . $tag);
  }

  /**
   * Gets the tag data from the given content.
   *
   * @param string $tag
   *   The HTML tag name.
   * @param string $content
   *   The markup string.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\EditorElement\SuggestionItem[]
   *   The list of the data stored in the given tag.
   */
  protected function getTagData(string $tag, string $content): array {
    $suggestions = $this->loadFromMarkup($tag, $content);
    $data = [];

    $class = match ($tag) {
      static::TAG_SUGGESTION => SuggestionItem::class,
      static::TAG_COMMENT => CommentItem::class,
      default => NULL,
    };

    if (is_null($class)) {
      return $data;
    }

    foreach ($suggestions as $suggestion) {
      if (!$suggestion->hasAttribute('name')) {
        continue;
      }

      $data[] = new $class($suggestion->getAttribute('name'));
    }

    return $data;
  }

}
