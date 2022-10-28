<?php

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\ckeditor5_premium_features\CKeditorFieldKeyHelper;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\FieldableEntityInterface;

class NotificationContextHelper {

  public function getFullContext(FieldableEntityInterface $document, string $key, CollaborationEntityInterface $entity) {
    $context = self::getDocumentFieldContent($document, $key);

    return $this->getFullContextFromDocument($context, $entity);
  }

  public function getFullContextFromDocument(string $context, CollaborationEntityInterface $entity) {
    $thread = $this->renderEntityThread($entity);

    $snippets = [];
    if ($entity instanceof CommentInterface) {
      $snippets = $this->getHighlightedComment($context, $entity);
    }
    if ($entity instanceof SuggestionInterface) {
      $snippets = $this->getHighlightedSuggestion($context, $entity);
    }

    return [
      '#theme' => 'notification_message_single',
      '#context' => $snippets,
      '#thread' => $thread,
    ];
  }

  public function getDocumentMentionContext(FieldableEntityInterface $document, string $key, string $mentionMarker) {
    $context = self::getDocumentFieldContent($document, $key);

    $snippets = $this->getHighlightedDocumentMention($context, $mentionMarker);

    return [
      '#theme' => 'notification_message_single',
      '#context' => $snippets,
    ];
  }

  public static function getDocumentFieldContent(FieldableEntityInterface $document, string $key) {
    if (!$key) {
      return NULL;
    }

    $fields = $document->getFields();

    foreach ($fields as $fieldName => $field) {
      $values = $document->get($fieldName)->getValue();
      foreach ($values as $delta => $val) {
        $id = CKeditorFieldKeyHelper::getElementUniqueId('edit-' . $fieldName . '-' . $delta);
        if ($key == $id) {
          return $val['value'];
        }
      }
    }

    return NULL;
  }

  public function getHighlightedComment(string $context, CommentInterface $comment) {
    $threadID = $comment->getThreadId();

    $query="//comment-start[contains(@name,'$threadID')]";

    $result = [];

    foreach ($this->getHighlightedContext($context, $query) as $markup) {
      $fixedMarkup = str_replace('</comment-start>', '', $markup);
      $fixedMarkup = str_replace('<comment-end', '<span', $fixedMarkup);
      $fixedMarkup = str_replace('</comment-end>', '</comment-start>', $fixedMarkup);

      $result[] = [
        '#markup' =>  $fixedMarkup,
        '#allowed_tags' => self::getNotificationAllowedTags(),
      ];
    }

    return $result;
  }

  public function getHighlightedSuggestion(string $context, SuggestionInterface $suggestion) {
    $suggestionChain = $suggestion->getChain();

    $queryOrParts = [];
    foreach ($suggestionChain as $suggestion) {
      $chainSuggestionId = $suggestion->id();
      $queryOrParts[] = "contains(@name,'$chainSuggestionId')";
    }
    $query='//suggestion-start[' . implode(' or ', $queryOrParts) . ']';

    $result = [];

    foreach ($this->getHighlightedContext($context, $query) as $markup) {
      $fixedMarkup = str_replace('</suggestion-start>', '', $markup);
      $fixedMarkup = str_replace('<suggestion-end', '<span', $fixedMarkup);
      $fixedMarkup = str_replace('</suggestion-end>', '</suggestion-start>', $fixedMarkup);

      $result[] = [
        '#markup' =>  $fixedMarkup,
        '#allowed_tags' => self::getNotificationAllowedTags(),
      ];
    }

    return $result;

  }

  public function getHighlightedDocumentMention(string $context, string $mentionMarker) {
    $query = "//span[contains(@data-mention,'$mentionMarker')]";

    $snippets = [];
    foreach ($this->getHighlightedContext($context, $query) as $markup) {
      $snippets[] = [
        '#markup' => $markup,
      ];
    }

    return $snippets;
  }

  public function renderEntityThread(CollaborationEntityInterface $entity): array {
    $result = [];
    foreach ($entity->getThread() as $threadItem) {
      $result[] = [
        '#theme' => 'notification_thread_comment',
        '#comment' => $threadItem,
      ];
    }
    return $result;
  }

  public static function getNotificationAllowedTags(): array {
    return  array_merge(Xss::getAdminTagList(), [
      'suggestion-start',
      'suggestion-end',
      'comment-start',
      'comment-end',
    ]);
  }

  /**
   * Returns matching HTML elements list with addition class used for highlighting matched element.
   *
   * @param $context
   *   Source HTML content.
   * @param $query
   *   XPATH query that will be used for selecting matching HTML part.
   *
   * @return array
   */
  protected function getHighlightedContext($context, $query): array {
    $document = Html::load($context);

    $contextParts = [];

    $xpath = new \DOMXPath( $document);
    $matchingElements = $xpath->query($query);
    if (!empty($matchingElements)) {
      /** @var \DOMElement $element */
      foreach ($matchingElements as $element) {
        $element->setAttribute('class', $element->getAttribute('class') . ' highlight-item');

        $contextParts[] = $element->ownerDocument->saveXML($element->parentNode);
      }
    }

    return $contextParts;
  }

}
