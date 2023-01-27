<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Utility;

/**
 * Class suited for helping handling collaboration HTML.
 */
class HtmlHelper {

  /**
   * Checks element parents and returns one that is suitable for a context.
   *
   * @param \DOMElement $element
   *   Element to search the best parent node.
   *
   * @return \DOMNode
   *   Returns element parent node or element itself if no parent node found.
   */
  public function selectElementParentNode(\DOMElement $element): \DOMNode {
    $acceptingParentNodeTypes = array_flip([
      'div',
      'p',
      'table',
    ]);
    $overParentNodesTypes = array_flip([
      'blockquote',
    ]);
    $topLevelTags = array_flip([
      'body',
      'html',
    ]);

    $parentNode = $element->parentNode;
    while ($parentNode) {
      $grandParentNode = $parentNode->parentNode;
      if (isset($overParentNodesTypes[$grandParentNode->nodeName])) {
        $parentNode = $grandParentNode;
        break;
      }
      if (isset($acceptingParentNodeTypes[$parentNode->nodeName]) || $grandParentNode == NULL) {
        break;
      }
      if (isset($topLevelTags[$grandParentNode->nodeName])) {
        break;
      }
      $value = $parentNode->nodeValue;
      if (mb_strlen(strip_tags($value)) > 255) {
        break;
      }
      $parentNode = $grandParentNode;
    }

    if (!$parentNode || ($parentNode && isset($topLevelTags[$parentNode->nodeName]))) {
      return $element;
    }

    return $parentNode;
  }

  /**
   * Removes collaboration entities not matching passed selector.
   *
   * @param \DOMDocument $document
   *   Document to be processed.
   * @param string $elementType
   *   Type of elements to search for.
   * @param string $selector
   *   Selector used for filtering not matching elements.
   */
  public function removeNotRequiredCollaborationElements(\DOMDocument $document, string $elementType, string $selector): void {
    $removeQueries = [];
    $postfix = [
      'start',
      'end',
    ];

    foreach ($postfix as $type) {
      $removeQueries[] = "//$elementType-$type" . "[not($selector)]";
    }

    $this->doRemoveElements($document, $removeQueries);
  }

  /**
   * Removes collaboration entities having data-suggestion- prefixed attributes
   * not matching passed selector.
   *
   * @param \DOMDocument $document
   *   Document to be processed.
   * @param string $selector
   *   Selector used for filtering not matching elements.
   */
  public function removeNotRequiredCollaborationElementsWithSuggestionAttributes(\DOMDocument $document, string $selector): void {
    $removeQueries = [];
    $attributes = [
      'data-suggestion-start-before',
      'data-suggestion-end-after',
    ];

    foreach ($attributes as $attribute) {
      $removeQueries[] = "//*[@$attribute][not($selector)]";
    }

    $this->doRemoveElements($document, $removeQueries);
  }

  /**
   * Removes collaboration entities matching passed queries.
   *
   * @param \DOMDocument $document
   *   Document to be processed.
   * @param array $removeQueries
   *   Array of queries defining entities ro remove.
   */
  private function doRemoveElements(\DOMDocument $document, array $removeQueries): void {
    $xpath = new \DOMXPath($document);

    foreach ($removeQueries as $queryR) {
      $commentsToRemove = $xpath->query($queryR);
      /** @var \DOMElement $elementToRemove */
      foreach ($commentsToRemove as $elementToRemove) {
        $elementToRemove->replaceWith(' ');
      }
    }
  }

  /**
   * Converts collaboration tags to HTML tags wrapping collaboration content.
   *
   * @param \DOMDocument $document
   *   Document to be processed.
   * @param string $elementType
   *   Type of elements to search for.
   * @param string $selector
   *   Selector used for filtering matching elements.
   */
  public function convertCollaborationTagsWrappings(\DOMDocument $document, string $elementType, string $selector): void {
    $queryStart = "//$elementType-start[$selector]";
    $queryEnd = "//$elementType-end[$selector]";

    $xpath = new \DOMXPath($document);

    $startingElement = $xpath->query($queryStart)->item(0);
    $endingElement = $xpath->query($queryEnd)->item(0);

    if (!isset($startingElement->parentNode) || !isset($endingElement->parentNode) ||
      $startingElement->parentNode->getNodePath() === $endingElement->parentNode->getNodePath()) {
      return;
    }

    $startingElementPath = explode('/', $startingElement->getNodePath());
    $endingElementPath = explode('/', $endingElement->getNodePath());
    $intersectedPart = [];
    foreach ($startingElementPath as $pathPos => $pathItem) {
      if (!isset($endingElementPath[$pathPos]) || $endingElementPath[$pathPos] != $pathItem) {
        break;
      }
      $intersectedPart[] = $endingElementPath[$pathPos];
    }
    $commonParentPath = implode('/', $intersectedPart);

    while ($startingElement->parentNode && $startingElement->parentNode->getNodePath() !== $commonParentPath) {
      $startingElement->parentNode->appendChild($endingElement->cloneNode(TRUE));

      if ($startingElement->parentNode->nextSibling) {
        $startingElement->parentNode->parentNode->insertBefore(
          $startingElement->cloneNode(TRUE),
          $startingElement->parentNode->nextSibling
        );
      }
      elseif ($startingElement->parentNode->parentNode) {
        $startingElement->parentNode->parentNode->appendChild($startingElement->cloneNode(TRUE));
      }

      if ($startingElement->parentNode->parentNode == NULL || $startingElement->parentNode->parentNode->getNodePath() == $commonParentPath) {
        break;
      }
      $startingElement = $startingElement->parentNode;
    }

    while ($endingElement->parentNode && $endingElement->parentNode->getNodePath() !== $commonParentPath) {
      $endingElement->parentNode->insertBefore(
        $startingElement->cloneNode(TRUE),
        $endingElement->parentNode->firstChild
      );

      $endingElement->parentNode->parentNode->insertBefore($endingElement->cloneNode(TRUE), $endingElement->parentNode);

      if ($endingElement->parentNode->parentNode == NULL || $endingElement->parentNode->parentNode->getNodePath() == $commonParentPath) {
        break;
      }
      $endingElement = $endingElement->parentNode;
    }
  }

  /**
   * Returns inner HTML for an element.
   *
   * @param \DOMDocument $document
   *   Document to be processed.
   * @param string $query
   *   Query to search for an element from which we should grab inner HTML.
   */
  public function getInnerHtml(\DOMDocument $document, string $query = '//body'): string {
    $xpath = new \DOMXPath($document);

    $bodyElement = $xpath->query($query)->item(0);

    $fixedMarkup = '';

    foreach ($bodyElement->childNodes as $node) {
      $fixedMarkup .= $document->saveHTML($node);
    }

    return $fixedMarkup;
  }

}
