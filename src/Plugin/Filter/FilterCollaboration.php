<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Plugin\Filter;

use Drupal\ckeditor5_premium_features\Utility\DomSuggestion;
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
 *   title = @Translation("Removes the collaboration (suggestions, comments) data from the markup so that the content displayed to your end users did not contain comments/suggestions for content editors."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   weight = -100
 * )
 */
class FilterCollaboration extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);

    $this->filterComments($xpath);
    $this->filterSuggestionsTags($xpath);
    $this->filterSuggestionsAttributes($xpath);

    $dom->saveHTML();
    $text = Html::serialize($dom);

    return new FilterProcessResult($text);
  }

  /**
   * Filter out the comment tags and attributes.
   *
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   */
  public function filterComments(\DOMXPath $xpath): void {
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

    $comments_attributes = [
      'data-comment-start-before',
      'data-comment-end-after',
    ];

    foreach ($comments_attributes as $attribute) {
      $elements = $xpath->query("//*[@$attribute]");
      if (!$elements) {
        continue;
      }

      /** @var \DOMElement $element */
      foreach ($elements as $element) {
        $element->removeAttribute($attribute);
      }
    }
  }

  /**
   * Filter out the suggestion tags or attributes (where needed).
   *
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   */
  public function filterSuggestionsTags(\DOMXPath $xpath): void {
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
        $dom_suggestion = new DomSuggestion($suggestion);
        if ($dom_suggestion->isInsertion() && $dom_suggestion->isStartTag()) {
          $this->removeUntilEnd($suggestion, $dom_suggestion->getNameAttributeValue());
        }
        else {
          try {
            if (!$suggestion || !$suggestion->parentNode) {
              continue;
            }
            $suggestion->remove();
          }
          catch (\Throwable) {
            continue;
          }
        }
      }
    }
  }

  /**
   * Filter out the suggestion attributes.
   *
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   */
  public function filterSuggestionsAttributes(\DOMXPath $xpath): void {
    $attributes = [
      'start' => 'data-suggestion-start-before',
      'end' => 'data-suggestion-end-after',
    ];

    $suggestions = $xpath->query("//*[@{$attributes['start']}]");

    if (!$suggestions) {
      return;
    }

    /** @var \DOMElement $suggestion */
    foreach ($suggestions as $suggestion) {
      $dom_suggestion = new DomSuggestion($suggestion);
      if ($dom_suggestion->isInsertion()) {
        $suggestion->remove();
      }
      else {
        foreach ($attributes as $attribute) {
          $suggestion->removeAttribute($attribute);
        }
      }
    }
  }

  /**
   * Removes all DOM elements and text until met the end tag or attribute.
   *
   * @param \DOMElement|\DOMText $element
   *   The DOM element.
   * @param string $name
   *   The name attribute value.
   */
  public function removeUntilEnd(\DOMElement|\DOMText $element, string $name = ''): void {
    $next = $element->nextSibling;
    $parent = $element->parentNode;
    $element->remove();

    if (empty($next)) {
      $next = $parent?->nextSibling?->firstChild;
      if (!$parent?->hasChildNodes()) {
        $parent->remove();
      }
    }

    if ($element instanceof \DOMElement) {
      $dom_element = new DomSuggestion($element);
      if ($dom_element->isEndTag() && $dom_element->hasName($name)) {
        // This is the end of the journey because of the html tag.
        return;
      }
      elseif ($dom_element->getEndAttributeValue() === $name) {
        // This is the end of the journey because of the attribute.
        return;
      }
    }

    if (!$next instanceof \DOMNode) {
      // Something went wrong, stop further processing.
      return;
    }

    $this->removeUntilEnd($next, $name);
  }

}
