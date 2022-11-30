<?php

namespace Drupal\ckeditor5_premium_features_notifications\Utility;

use Drupal\ckeditor5_premium_features\CKeditorFieldKeyHelper;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface;
use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Class offering helper methods for collecting notification context.
 */
class NotificationContextHelper {

  /**
   * Collects a context for a collaboration entity using a document entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $document
   *   Document entity.
   * @param string $key
   *   Unique key ID to collect document value from.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface $entity
   *   Collaboration entity.
   *
   * @return array
   */
  public function getFullContext(FieldableEntityInterface $document, string $key, CollaborationEntityInterface $entity): array {
    $context = self::getDocumentFieldContent($document, $key);

    return $this->getFullContextFromDocument($context, $entity);
  }

  /**
   * Collects a context for a collaboration entity.
   *
   * @param string $context
   *   A string with a document content.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface $entity
   *   Collaboration entity.
   *
   * @return array
   */
  public function getFullContextFromDocument(string $context, CollaborationEntityInterface $entity): array {
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

  /**
   * Collects a context for a mention found in a document.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $document
   *   Document entity.
   * @param string $key
   *   Unique key ID to collect document value from.
   * @param string $mentionMarker
   *   Mention marker that should be found in a document.
   */
  public function getDocumentMentionContext(FieldableEntityInterface $document, string $key, string $mentionMarker, string $originalContent = NULL): array {
    $context = !empty($originalContent) ? $originalContent : self::getDocumentFieldContent($document, $key);

    $snippets = $this->getHighlightedDocumentMention($context, $mentionMarker);

    return [
      '#theme' => 'notification_message_single',
      '#context' => $snippets,
    ];
  }

  /**
   * Search for a field matching the key parameter and returns its value.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $document
   *   Document entity with fields.
   * @param string $key
   *   Unique field key.
   *
   * @return mixed|null
   */
  public static function getDocumentFieldContent(FieldableEntityInterface $document, string $key): ?string {
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

  /**
   * Prepares a render array with highlighted suggestion markup.
   *
   * @param string $context
   *   Document content.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\CommentInterface $comment
   *   Comment to be highlighted.
   */
  public function getHighlightedComment(string $context, CommentInterface $comment): array  {
    $threadID = $comment->getThreadId();

    $query="//comment-start[contains(@name,'$threadID')]";

    $result = [];

    foreach ($this->getMatchingContext($context, $query) as $markup) {
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

  /**
   * Prepares a render array with highlighted suggestion markup.
   *
   * @param string $context
   *   Document content.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $suggestion
   *   Suggestion to be highlighted.
   */
  public function getHighlightedSuggestion(string $context, SuggestionInterface $suggestion): array {
    $suggestionChain = $suggestion->getChain();

    $queryOrParts = [];
    foreach ($suggestionChain as $suggestion) {
      $chainSuggestionId = $suggestion->id();
      $queryOrParts[] = "contains(@name,'$chainSuggestionId')";
    }
    $query='//suggestion-start[' . implode(' or ', $queryOrParts) . ']';

    $result = [];

    foreach ($this->getMatchingContext($context, $query) as $markup) {
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

  /**
   * Prepares a render array with highlighted the mention markup.
   *
   * @param string $context
   *   Document content.
   * @param string $mentionMarker
   *   Mention marker to be highlighted.
   */
  public function getHighlightedDocumentMention(string $context, string $mentionMarker): array {
    $query = "//span[contains(@data-mention,'$mentionMarker')]";

    $snippets = [];
    foreach ($this->getMatchingContext($context, $query) as $markup) {
      $snippets[] = [
        '#markup' => $markup,
      ];
    }

    return $snippets;
  }

  /**
   * Prepares a render array with highlighted document detected changes.
   *
   * @param string $context
   *   Document content.
   * @param bool $onlyInserts
   *   Flag for determining type of changes to be selected.
   */
  public function getHighlightedDocumentChanges(string $context, bool $onlyInserts = FALSE): array {
    $query = "//ins" . ($onlyInserts ? '' : '|//del');

    $snippets = [];
    foreach ($this->getMatchingContext($context, $query, FALSE) as $markup) {
      $snippets[] = [
        '#markup' => $markup,
      ];
    }

    return [
      '#theme' => 'notification_message_single',
      '#context' => $snippets,
    ];
  }

  /**
   * Returns an array of strings with document detected changes.
   *
   * @param string $context
   *   Document content.
   * @param bool $onlyInserts
   *   Flag for determining type of changes to be selected.
   */
  public function getDocumentChangesContext(string $context, bool $onlyInserts = FALSE): array {
    $query = "//ins" . ($onlyInserts ? '' : '|//del');

    $snippets = [];
    foreach ($this->getMatchingContext($context, $query, FALSE) as $markup) {
      $snippets[] = $markup;
    }

    return $snippets;
  }

  /**
   * Returns a render array with the collaboration entity thread.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\CollaborationEntityInterface $entity
   *   Collaboration entity that is a part of a thread.
   *
   * @return array
   */
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

  /**
   * Returns a list of additional collaboration tags.
   *
   * @return array
   */
  public static function getNotificationAllowedTags(): array {
    return  array_merge(Xss::getAdminTagList(), [
      'suggestion-start',
      'suggestion-end',
      'comment-start',
      'comment-end',
    ]);
  }

  /**
   * Returns matching HTML elements list with optional addition class used for highlighting matched element.
   *
   * @param string $context
   *   Source HTML content.
   * @param string $query
   *   XPATH query that will be used for selecting matching HTML part.
   * @param bool $highlight
   *
   * @return array
   */
  protected function getMatchingContext(string $context, string $query, bool $highlight = TRUE): array {
    $document = Html::load($context);

    $contextParts = [];

    $xpath = new \DOMXPath( $document);
    $matchingElements = $xpath->query($query);
    if (!empty($matchingElements)) {
      /** @var \DOMElement $element */
      foreach ($matchingElements as $element) {
        if ($highlight) {
          $element->setAttribute('class', $element->getAttribute('class') . ' highlight-item');
        }
        // Let's prevent selecting same parent node several times (when several changes were made in the same paragraph tag).
        $parentNode = $this->selectElementParentNode($element);
        $parentPath = $parentNode->getNodePath();
        $matched = FALSE;
        foreach ($contextParts as $nodePath => $html) {
          if (stripos($nodePath, $parentPath) !== FALSE) {
            // Let's prefer to choose parent node instead of it;s children.
            unset($contextParts[$nodePath]);
          }
          elseif (stripos($parentPath, $nodePath) !== FALSE) {
            // Let's also detect a situation when we select a child of a parent that we already selected.
            $matched = TRUE;
            break;
          }
        }
        if (!$matched) {
          $contextParts[$parentPath] = $element->ownerDocument->saveXML($parentNode);
        }
      }
    }

    return $contextParts;
  }

  /**
   * Checks passed element parent nodes and returns the that is enough to representing its context.
   *
   * @param \DOMElement $element
   *   Element to search the best parent node.
   *
   * @return \DOMElement|\DOMNode
   *   Returns element parent node or element itself if no parent node found.
   */
  protected function selectElementParentNode(\DOMElement $element) {
    $acceptingParentNodeTypes = array_flip([
      'div',
      'p',
      'table'
    ]);

    $parentNode = $element->parentNode;
    while ($parentNode) {
      if (isset($acceptingParentNodeTypes[$parentNode->nodeName]) || $parentNode->parentNode == NULL) {
        break;
      }
      $value = $parentNode->nodeValue;
      if (mb_strlen(strip_tags($value)) > 255) {
        break;
      }
      $parentNode = $parentNode->parentNode;
    }

    if (!$parentNode) {
      return $element;
    }

    return $parentNode;
  }

}
