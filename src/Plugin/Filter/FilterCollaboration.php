<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Plugin\Filter;

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
    $this->convertSuggestionsAttributes($dom, $xpath);

    $dom->saveHTML();
    $text = Html::serialize($dom);

    $this->filterSuggestionsTags($text);

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
   * Filter out the suggestion tags.
   *
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   */
  public function filterSuggestionsTags(string &$text): void {
    $text = preg_replace('#<suggestion-start[^<>]*insertion[^<>]*></suggestion-start>#si', '<ins>', $text);
    $text = preg_replace('#<suggestion-end[^<>]*insertion[^<>]*></suggestion-end>#si', '</ins>', $text);
    $text = preg_replace('%(<ins.*?>)(.*?)(<\/ins.*?>)%is', '', $text);

    $text = preg_replace('#<suggestion-start[^<>]*></suggestion-start>#si', '', $text);
    $text = preg_replace('#<suggestion-end[^<>]*></suggestion-end>#si', '', $text);
  }

  /**
   * Replaces the suggestion attributes with suggestion tags.
   *
   * @param \DOMDocument $dom
   *   The DOM Document.
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   */
  public function convertSuggestionsAttributes(\DOMDocument $dom, \DOMXPath $xpath): void {
    $attributes = [
      'end-before' => 'data-suggestion-end-before',
      'start-before' => 'data-suggestion-start-before',
      'start-after' => 'data-suggestion-start-after',
      'end-after' => 'data-suggestion-end-after',
    ];

    foreach ($attributes as $key => $attribute) {
      $queryExpression = "//*[@{$attribute}]";
      $suggestions = $xpath->query($queryExpression);

      if (!$suggestions) {
        return;
      }

      /** @var \DOMElement $suggestion */
      foreach ($suggestions as $suggestion) {
        switch ($key) {
          case 'start-before':
            $this->replaceSuggestionAttribute($dom, $suggestion, $attribute, 'suggestion-start', 'before');
            break;
          case 'start-after':
            $this->replaceSuggestionAttribute($dom, $suggestion, $attribute, 'suggestion-start', 'after');

            break;
          case 'end-before':
            $this->replaceSuggestionAttribute($dom, $suggestion, $attribute, 'suggestion-end', 'before');

            break;
          case 'end-after':
            $this->replaceSuggestionAttribute($dom, $suggestion, $attribute, 'suggestion-end', 'after');
            break;

        }
      }
    }
  }

  /**
   * Replace data-suggestion attributes with suggestion tags.
   *
   * This allows for easier and less prone for errors filtering of suggestions.
   *
   * @param \DOMDocument $dom
   *   The Dom document.
   * @param \DOMElement $suggestion
   *   An element to process.
   * @param string $attribute
   *   An attribute name to process.
   * @param string $name
   *   The tag name to create in place of attribute.
   * @param string $function
   *   Function to apply on element to place new tag in correct place.
   *   Most times it'll be 'before' or 'after'.
   * @return void
   * @throws \DOMException
   */

  private function replaceSuggestionAttribute(\DOMDocument $dom, \DOMElement $suggestion, string $attribute, string $qualifiedName, string $function): void {
    $value = $suggestion->getAttribute($attribute);
    $elem = new \DOMElement($qualifiedName);
    $elemNode = $dom->importNode($elem);
    $elemNode->setAttribute('name', $value);
    $suggestion->$function($elemNode);
    $suggestion->removeAttribute($attribute);
  }

}
