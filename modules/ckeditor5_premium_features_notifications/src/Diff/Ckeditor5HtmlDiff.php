<?php

namespace Drupal\ckeditor5_premium_features_notifications\Diff;

use Caxy\HtmlDiff\HtmlDiff;
use Drupal\Component\Utility\Html;

class Ckeditor5HtmlDiff extends HtmlDiff {

  public function getAddedContent(): string {
    $addedParts = [];
    $operations = $this->operations();
    foreach ($operations as $operation) {
      switch ($operation->action) {
        case 'insert' :
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

    $tempRes = implode(PHP_EOL, $addedParts);

    $document = Html::load($tempRes);

    $xpath = new \DOMXPath( $document);
    $bodyElement = $xpath->query('//body')->item(0);

    $htmlRes = '';

    foreach ($bodyElement->childNodes as $node) {
      $htmlRes .= $document->saveHTML($node);
    }

    return $htmlRes;
  }

  public function getContext() :string {
    return $this->content;
  }
}
