<?php

namespace Drupal\ckeditor5_premium_features_notifications\Diff\Renderer;

use jblond\Diff\Renderer\MainRenderer;

class Ckeditor5DiffBasicRenderer extends MainRenderer
{

  /**
   * {@inheritdoc}
   */
  public function render()
  {
    $changes = parent::renderSequences();

    return $this->renderOutput($changes, $this);
  }

  /**
   * {@inheritdoc}
   */
  public function renderOutput(array $changes, object $subRenderer) {
    if (!$changes) {
      return false;
    }

    $output = [];

    foreach ($changes as $blocks) {
      foreach ($blocks as $change) {
        switch ($change['tag']) {
          case 'insert':
          case 'replace':
            $output[] = $subRenderer->generateNewContent($change);
            break;
        }
      }
    }

    return $this->cleanupResultLines($output);
  }

  /**
   * Returns a string containing lines with added content.
   *
   * @param array $changes
   *   Changes list.
   */
  public function generateNewContent(array $changes): string {
    $output = [];

    foreach ($changes['changed']['lines'] as $changedLine) {
      $changedLine = str_replace(["\0", "\1"], $this->options['insertMarkers'], $changedLine);

      $output[] =  $changedLine;
    }

    return implode(PHP_EOL, $output);
  }

  /**
   * Cleans-up the lines removing old content surrounding the newly inserted parts.
   *
   * @param array|string $lines
   *   An array of lines or a single string with modified content.
   *
   * @return string
   *   Returns a string with imploded lines (PHP_EOL character ued form joining).
   */
  protected function cleanupResultLines($lines): string {
    $openMarker = $this->options['insertMarkers'][0];
    $closeMarker = $this->options['insertMarkers'][1];

    $regexpBefore = "(.(?<!$openMarker|$closeMarker))*$openMarker";
    $regexpAfter = "$closeMarker" . "((?!$openMarker|$closeMarker).)*";
    $regexpBetween = "/$openMarker(((?!$openMarker|$closeMarker).)*)$closeMarker/si";

    $result = [];
    foreach ($lines as $line) {
      $matches = [];
      if (!preg_match_all($regexpBetween, $line, $matches) || empty($matches[1][0])) {
        continue;
      }
      $result[] = $matches[1][0];
    }
//    // Here we are removing text that is surrounding the newly inserted content.
//    $lines = preg_match("/$regexpBetween/si", PHP_EOL, $lines);
//    $lines = preg_replace("/$regexpBefore/si", PHP_EOL, $lines);
//    $lines = preg_replace("/$regexpAfter/si", PHP_EOL, $lines);

    return trim(preg_replace('/\n+/', PHP_EOL, implode(PHP_EOL, $result)));
  }

}
