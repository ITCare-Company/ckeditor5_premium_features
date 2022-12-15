<?php

namespace Drupal\ckeditor5_premium_features_notifications\Diff;

use Caxy\HtmlDiff\HtmlDiff;
use Drupal\Component\Utility\Html;

/**
 * Ckeditor5 extension of an external library for detecting string changes.
 */
class Ckeditor5HtmlDiff extends HtmlDiff {

  /**
   * Returns a string with content parts, added to the compared document.
   */
  public function getAddedContent(): string {
    $addedParts = [];
    $operations = $this->operations();
    foreach ($operations as $operation) {
      switch ($operation->action) {
        case 'insert':
        case 'replace':
          $newWordsImploded = implode('', array_slice(
            $this->newWords,
            $operation->startInNew,
            $operation->endInNew - $operation->startInNew
          ));
          $addedParts[] = $newWordsImploded;
          break;
      }
    }

    $allNewContentParts = implode(PHP_EOL, $addedParts);

    return $this->fixHtmlWithPotentiallyImproperHtml($allNewContentParts);
  }

  /**
   * Returns string representing document with marked detected changes.
   */
  public function getContext() :string {
    return $this->content;
  }

  /**
   * Process passed HTML to try to fix bad HTML.
   *
   * @param string $htmlString
   *   String to be processed using \DOMDocument.
   */
  protected function fixHtmlWithPotentiallyImproperHtml(string $htmlString): string {
    $document = Html::load($htmlString);

    $xpath = new \DOMXPath($document);
    $bodyElement = $xpath->query('//body')->item(0);

    $htmlRes = '';

    foreach ($bodyElement->childNodes as $node) {
      $htmlRes .= $document->saveHTML($node);
    }

    return $htmlRes;
  }

}
