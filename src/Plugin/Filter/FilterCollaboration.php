<?php

/*
 * Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Plugin\Filter;

use Drupal\ckeditor5_premium_features\Utility\ApiAdapter;
use Drupal\ckeditor5_premium_features\Utility\Html;
use Drupal\ckeditor5_premium_features\Utility\HtmlHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to cleanup the collaboration features markup data.
 *
 * Simply removes the markup and it's content for the not yet approved
 * changes and comments.
 *
 * @Filter(
 *   id = "ckeditor5_premium_features_collaboration_filter",
 *   title = @Translation("Removes the collaboration (suggestions, comments) data from the markup so that the content displayed to your end users does not contain comments/suggestions for content editors."),
 *   description = @Translation("This filter should be executed as soon as possible. If you encounter missing whitespaces near words that contain suggestions please move it up in the filter processing order."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   weight = -100
 * )
 */
class FilterCollaboration extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * Array mapping CKEditor attribute suggestion names to HTML tags.
   */
  const TAG_SUGGESTIONS = [
    'bold' => 'strong',
    'italic' => 'em',
    'underline' => 'u',
    'strikethrough' => 's',
    'code' => 'code',
    'superscript' => 'sup',
    'subscript' => 'sub',
    'highlight' => 'mark',
  ];

  /**
   * Array mapping CKEditor attribute suggestion names to style properties.
   */
  const STYLE_SUGGESTIONS = [
    'fontColor' => 'color',
    'fontFamily' => 'font-family',
    'fontSize' => 'font-size',
    'fontBackgroundColor' => 'background-color',
  ];

  /**
   * Array of lists related attributes.
   */
  const LIST_ATTRIBUTES = [
    'listType',
    'listStart',
    'listReversed',
  ];

  /**
   * The HTML helper.
   *
   * @var \Drupal\ckeditor5_premium_features\Utility\HtmlHelper
   */
  protected $htmlHelper;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The CKEditor cloud services API adapter.
   *
   * @var \Drupal\ckeditor5_premium_features\Utility\ApiAdapter
   */
  protected $apiAdapter;

  /**
   * The suggestions data.
   *
   * @var array
   */
  protected $suggestionsData = [];

  /**
   * Constructs a new FilterCollaboration.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Definition.
   * @param \Drupal\ckeditor5_premium_features\Utility\HtmlHelper $html_helper
   *   The HTML helper.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The Module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity type manager.
   * @param \Drupal\ckeditor5_premium_features\Utility\ApiAdapter $api_adapter
   *   The CKEditor cloud services API adapter.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, HtmlHelper $html_helper, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, ApiAdapter $api_adapter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->htmlHelper = $html_helper;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->apiAdapter = $api_adapter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('ckeditor5_premium_features.html_helper'),
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('ckeditor5_premium_features.api_adapter')
    );
  }

  /**
   * Get HTML helper utility service.
   *
   * @return \Drupal\ckeditor5_premium_features\Utility\HtmlHelper
   */
  public function getHtmlHelper(): HtmlHelper {
    return $this->htmlHelper;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return $this->doProcess($text);
  }

  /**
   * Process the text with the track changes data. This is used for processing before updated entity is saved (validation).
   * And new suggestions aren't saved yet.
   *
   * @param string $text
   *   The text to process.
   * @param array $trackChangesData
   *   The track changes data.
   *
   * @return \Drupal\filter\FilterProcessResult
   *   The processed text.
   */
  public function processWithTrackChangesData($text, array $trackChangesData = []) {
    return $this->doProcess($text, $trackChangesData);
  }

  /**
   * Process the text. The result will have the suggestions and comments removed and the formatting suggestions will be
   * reverted.
   *
   * @param string $text
   *   The text to process.
   * @param array $trackChangesData
   *   The track changes data.
   *
   * @return \Drupal\filter\FilterProcessResult
   *   The processed text.
   */
  private function doProcess($text, array $trackChangesData = []) {
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);

    $this->filterComments($xpath);
    $this->htmlHelper->convertSuggestionsAttributes($dom, $xpath);
    if ($trackChangesData) {
      $this->processSuggestionsData($trackChangesData);
    }
    else {
      $this->loadAttributeSuggestions($dom, $xpath);
    }
    if ($this->suggestionsData) {
      $this->processAttributeSuggestions($dom, $xpath);
    }

    $dom->saveHTML();
    $text = Html::serialize($dom);
    $this->filterSuggestionsTags($text);

    return new FilterProcessResult($text);
  }

  /**
   * Filter out the suggestion styling tags from the document.
   *
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   * @param \DOMDocument $dom
   *   The DOM Document.
   */
  public function filterStyleSuggestion(\DOMXPath $xpath, \DOMDocument $dom): void {
    $query = '//suggestion-start[contains(@name, "attribute:")]/following-sibling::node()[following-sibling::suggestion-end[contains(@name, "attribute:")]]';
    $suggestions = $xpath->query($query);
    foreach ($suggestions as $suggestion) {
      if ($suggestion instanceof \DOMElement) {
        $textNode = $dom->createTextNode($suggestion->textContent);
        $suggestion->parentNode->replaceChild($textNode, $suggestion);
      }
    }
  }

  /**
   * Determines the attribute suggestion type and applies the proper processing function.
   *
   * @param \DOMDocument $dom
   *   The DOM Document.
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   */
  public function processAttributeSuggestions(\DOMDocument $dom, \DOMXPath $xpath): void {
    foreach ($this->suggestionsData as $suggestion) {
      $data = $suggestion['data'];
      $key = $data['key'];
      $tag = $this::TAG_SUGGESTIONS[$key] ?? NULL;
      $style = $this::STYLE_SUGGESTIONS[$key] ?? NULL;
      $list = in_array($key, $this::LIST_ATTRIBUTES) ? $key : NULL;

      if ($tag) {
        $this->processTagAttributeSuggestions($dom, $xpath, $suggestion);
      }
      elseif ($style) {
        $this->processStyleAttributeSuggestions($dom, $xpath, $suggestion);
      }
      elseif ($list) {
        $this->processListAttributeSuggestions($dom, $xpath, $suggestion);
      }
    }
  }

  /**
   * Load the suggestions data for attribute (formatting or style change) suggestions for the suggestions that are present
   * in the document.
   *
   * @param \DOMDocument $dom
   *   The DOM Document.
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   */
  private function loadAttributeSuggestions(\DOMDocument $dom, \DOMXPath $xpath): void {
    // Search for end elements as start can be a property within a tag additionally having more than one start suggestions.
    $endElements = $xpath->query('//suggestion-end[contains(@name, "attribute:")]');
    if ($endElements->length == 0) {
      return;
    }
    $suggestionIds = array_map(function ($endElement) {
      $nameValue = $endElement->getAttribute('name');
      return explode(':', $nameValue)[2];
    }, iterator_to_array($endElements));

    if ($this->moduleHandler->moduleExists('ckeditor5_premium_features_collaboration')) {
      $suggestions = $this->entityTypeManager->getStorage('ckeditor5_suggestion')->loadMultiple($suggestionIds);
      foreach ($suggestions as $suggestion) {
        $this->suggestionsData[$suggestion->id()] = $suggestion->toArray();
      }
    }
    elseif ($this->moduleHandler->moduleExists('ckeditor5_premium_features_realtime_collaboration')) {
      // With RTC we do not store suggestions dats so those should be retrieved with cloud API.
      $documentElement = $xpath->query('//*[@data-document-id]');
      if ($documentElement->length == 0) {
        return;
      }
      $documentElement = $documentElement->item(0);
      $documentId = $documentElement->getAttribute('data-document-id');
      $suggestions = $this->apiAdapter->getDocumentSuggestions(
        $documentId, [
          'sort_by' => 'updated_at',
          'order' => 'desc',
          'limit' => 1000,
        ]
      );
      foreach ($suggestions as $suggestion) {
        if (in_array($suggestion['id'], $suggestionIds)) {
          $this->suggestionsData[$suggestion['id']] = $suggestion;
        }
      }

      $documentElement->parentNode->removeChild($documentElement);
    }
  }

  /**
   * Process suggestions that are wrapping text with a tag (for example <strong>, <em>).
   *
   * @param \DOMDocument $dom
   *   The DOM Document.
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   * @param array $suggestion
   *   The suggestion data.
   */
  private function processTagAttributeSuggestions(\DOMDocument $dom, \DOMXPath $xpath, array $suggestion): void {
    $name = $suggestion['type'] . ':' . $suggestion['id'];
    $startElement = $xpath->query('//suggestion-start[contains(@name, "' . $name . '")]')->item(0);
    $endElement = $xpath->query('//suggestion-end[contains(@name, "' . $name . '")]')->item(0);

    if ($endElement) {
      $data = $suggestion['data'];
      $tag = $this::TAG_SUGGESTIONS[$data['key']] ?? '';

      if (!$tag) {
        return;
      }

      $currentNode = $startElement->nextSibling;
      $nodesToProcess = [];
      $textNodes = [];
      while ($currentNode && $currentNode !== $endElement) {

        if ($currentNode  instanceof \DOMElement && $currentNode->tagName == $tag) {
          $nodesToProcess[] = $currentNode;
        }
        elseif ($currentNode instanceof \DOMText) {
          $textNodes[] = $currentNode;
        }

        if ($currentNode  instanceof \DOMElement && $currentNode->childElementCount > 0) {
          $currentNode = $currentNode->firstChild;
        }
        elseif ($currentNode->nextSibling) {
          $currentNode = $currentNode->nextSibling;
        }
        else {
          $currentNode = $currentNode->parentNode->nextSibling;
        }
      }

      if (empty($nodesToProcess)) {
        foreach ($textNodes as $textNode) {
          $this->htmlHelper->wrapTextWithTag($dom, $textNode, $tag);
        }
      }
      foreach ($nodesToProcess as $node) {

        if ($node instanceof \DOMText) {
          $newElement = $dom->createElement($tag);
          $newElement->textContent = $node->textContent;
          $node->parentNode->replaceChild($newElement, $node);
        } elseif ($node instanceof \DOMElement) {
          $this->htmlHelper->removeWrappingElement($node);
        }
      }
    }
  }

  /**
   * Process suggestions that are changing the style of the text and revert previous state.
   *
   * @param \DOMDocument $dom
   *   The DOM Document.
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   * @param array $suggestion
   *   The suggestion data.
   */
  private function processStyleAttributeSuggestions(\DOMDocument $dom, \DOMXPath $xpath, array $suggestion): void {
    $data = $suggestion['data'];
    $key = $data['key'];
    $property = $this::STYLE_SUGGESTIONS[$key] ?? NULL;
    if (!$property) {
      return;
    }

    $name = $suggestion['type'] . ':' . $suggestion['id'];
    $query = '//suggestion-start[contains(@name, "' . $name . '")]/following-sibling::node()[following-sibling::suggestion-end[contains(@name, "' . $name . ':")]]';
    $suggestionHTML = $xpath->query($query);

    if ($suggestionHTML->length == 0) {
      return;
    }

    $suggestionElement = $suggestionHTML->item(0);

    if ($suggestionElement instanceof \DOMElement) {
      while (isset($suggestionElement->tagName) && $suggestionElement->tagName == 'suggestion-start') {
        $suggestionElement = $suggestionElement->nextSibling;
      }
    }

    if ($suggestionElement instanceof \DOMText && $data['oldValue']) {
      $this->htmlHelper->wrapTextWithTag($dom, $suggestionElement, 'span', [$property => $data['oldValue']]);
    } elseif ($suggestionElement instanceof \DOMElement) {
      if ($suggestionElement->tagName == 'span') {
        $styleArray = $this->styleStringToArray($suggestionElement->getAttribute('style'));
        if ($data['oldValue']) {
          $styleArray[$property] = $data['oldValue'];
        } else {
          unset($styleArray[$property]);
        }
        $suggestionElement->setAttribute('style', $this->styleArrayToString($styleArray));
      } else {
        $this->htmlHelper->wrapElementWithTag($dom, $suggestionElement, 'span', [$property => $data['oldValue']]);
      }
    }
  }

  /**
   * Process the list attribute suggestions, i.e. list type change, start value change, reversed list.
   *
   * @param \DOMDocument $dom
   *   The DOM Document.
   * @param \DOMXPath $xpath
   *   The DOM XPath.
   * @param array $suggestion
   *   The suggestion data.
   */
  private function processListAttributeSuggestions(\DOMDocument $dom, \DOMXPath $xpath, array $suggestion): void {
    $data = $suggestion['data'];
    $name = $suggestion['type'] . ':' . $suggestion['id'];
    $element = $xpath->query('//suggestion-start[contains(@name, "' . $name . '")]')->item(0);
    $startElement = $element;
    while ($element && !in_array($element->tagName, ['ul', 'ol'])) {
      $element = $element->parentNode;
    }
    if (!$element) {
      return;
    }

    switch ($data['key']) {
      case 'listStart':
        if ($data['oldValue'] && $data['oldValue'] != 1) {
          $element->setAttribute('start', (string) $data['oldValue']);
        } else {
          $element->removeAttribute('start');
        }
        break;
      case 'listType':
        $tag = $data['oldValue'] == 'numbered' ? 'ol' : 'ul';
        $this->htmlHelper->changeElementTag($dom, $element, $tag);
        break;
      case 'listReversed':
        if ($data['oldValue']) {
          $element->setAttribute('reversed', 'reversed');
        } else {
          $element->removeAttribute('reversed');
        }
        break;
    }

    // List style suggestions are wrapped in a paragraph tag, remove it in case it doesn't have some additional attributes.
    if ($child = $startElement->nextSibling) {
      if ($child instanceof \DOMElement && $child->tagName == 'p' && !$child->hasAttributes()) {
        $this->htmlHelper->removeWrappingElement($child);
      }
    }
  }

  /**
   * Convert the style string to an array.
   *
   * @param string $styleString
   *   The style string.
   *
   * @return array
   *   The style array.
   */
  private function styleStringToArray(string $styleString): array {
    if (!$styleString) {
      return [];
    }

    $styles = explode(';', $styleString);
    $styleArray = [];
    foreach ($styles as $style) {
      if (!$style) {
        continue;
      }
      $styleParts = explode(':', $style);
      $styleArray[$styleParts[0]] = $styleParts[1];
    }
    return $styleArray;
  }

  /**
   * Convert the style array to a string.
   *
   * @param array $style
   *   The style array.
   *
   * @return string
   *   The style string.
   */
  private function styleArrayToString(array $style): string {
    if (!$style) {
      return '';
    }

    $styleString = '';
    foreach ($style as $property => $value) {
      $styleString .= $property . ':' . $value . ';';
    }
    return $styleString;
  }

  /**
   * Filters out suggestions that are already resolved.
   *
   * @param array $trackChangesData
   *   The track changes data.
   */
  private function processSuggestionsData(array $trackChangesData): void {
    $suggestionsData = [];
    foreach ($trackChangesData as $change) {
      if (isset($change['attributes']['status'])) {
        continue;
      }
      $suggestionsData[$change['id']] = $change;
    }
    $this->suggestionsData = $suggestionsData;
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

}
