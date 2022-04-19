<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to cleanup the collaboration features markup data.
 *
 * Simply removes the markup and and it's content for the not yet approved
 * changes and comments.
 *
 * @Filter(
 *   id = "ckeditor5_premium_features_collaboration_filter",
 *   title = @Translation("Removes the collaboration (suggestions, comments)
 *   data from the markup"), type =
 *   Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE, weight
 *   = -100
 * )
 */
class FilterCollaboration extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $dom = Html::load($text);
    $this->filterTags($dom);
//    $this->filterAttributes($markup);
    $dom->saveHTML();
    $text = Html::serialize($dom);
    return new FilterProcessResult($text);
  }

  public function filterTags(\DOMDocument $dom) {
    $xpath = new \DOMXPath($dom);
    $comment_tags = [
      'comment-start',
      'comment-end',
    ];

    foreach ($comment_tags as $comment_tag) {
      $comments = $xpath->query('//' . $comment_tag);
      if (!$comments) {
        continue;
      }

      /** @var \DOMElement $comment */
      foreach ($comments as $comment) {
        $comment->remove();
      }
    }

    $suggestions_tags = [
      'suggestion-start',
      'suggestion-end',
    ];

    foreach ($suggestions_tags as $suggestion_tag) {
      $suggestions = $xpath->query('//' . $suggestion_tag);

      if (!$suggestions) {
        continue;
      }

      /** @var \DOMElement $suggestion */
      foreach ($suggestions as $suggestion) {
        $name = $suggestion->getAttribute('name');
        [$type, $id, $user_id] = explode(':', $name);

        if ($type === 'insertion' && str_ends_with($suggestion_tag, '-start')) {
          $this->removeUntilEnd($suggestion, $name);
        }
        else {
          $suggestion->remove();
        }
      }
    }
  }

  public function filterAttributes(\DOMDocument $dom) {
    $suggestion_start_attribute = 'data-suggestion-start-before';
    $suggestion_end_attribute = 'data-suggestion-end-after';

    $xpath = new \DOMXPath($dom);
    $suggestions = $xpath->query("//*[@$suggestion_start_attribute]");
    if (!$suggestions) {
      return;
    }

    /** @var \DOMElement $suggestion */
    foreach ($suggestions as $suggestion) {
      $suggestion_start_data_value = $suggestion->getAttribute($suggestion_start_attribute);
      if ($suggestion_start_data_value === $suggestion->getAttribute($suggestion_end_attribute)) {
        [$type, $id, $uid] = explode(':', $suggestion_start_data_value);
        if ($type === 'insertion') {
          $suggestion->remove();
        }
        else {
          $suggestion->removeAttribute($suggestion_start_attribute);
          $suggestion->removeAttribute($suggestion_end_attribute);
        }
      }
    }
  }

  public function removeUntilEnd(\DOMElement|\DOMText $element, $name) {
    $next = $element->nextSibling;
    $parent = $element->parentNode;
    $element->remove();
    if (empty($next)) {
      $next = $parent?->nextSibling?->firstChild;
      if (!$parent?->hasChildNodes()) {
        $parent->remove();
      }
    }

    if ($element->nodeName === 'suggestion-end' && $element->getAttribute('name') === $name) {
      // This is end of the journey because of the html tag...
      return;
    }
    $attribute_name = 'data-suggestion-end-after';

    if ($element instanceof \DOMElement && $element->hasAttribute($attribute_name) && $element->getAttribute($attribute_name) === $name) {
      // This is end of the journey because of the attribute...
      return;
    }

    $this->removeUntilEnd($next, $name);
  }

}
