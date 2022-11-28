<?php

namespace Drupal\ckeditor5_premium_features_notifications\Diff;

use DiffMatchPatch\DiffMatchPatch;
use Drupal\ckeditor5_premium_features_notifications\Utility\NotificationContextHelper;
use Drupal\Core\Render\Renderer;

class Ckeditor5DiffBasic implements Ckeditor5DiffInterface {

  protected string $context;

  protected string $separator = ' [...] ';

  protected int $numberCharSurrounding = 250;

  public function __construct(
    protected NotificationContextHelper $contextHelper,
    protected Renderer $renderer
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getDiff(string $oldDocument, string $newDocument): ?string {
    $matchDiff = new DiffMatchPatch();

    $changes = $matchDiff->diff_main($oldDocument, $newDocument, FALSE);
    $matchDiff->diff_cleanupSemantic($changes);

    $changesAdded = $this->getAddedChanges($changes);

    if (empty($changesAdded)) {
      return NULL;
    }

    $diffHtml = htmlspecialchars_decode($matchDiff->diff_prettyHtml($changes));
    $highlight = $this->contextHelper->getHighlightedDocumentInserts($diffHtml);

    $this->context = $this->renderer->render($highlight);

    return trim(implode(PHP_EOL, $changesAdded));
  }

  /**
   * {@inheritdoc}
   */
  public function getDiffContext(): ?string {
    return htmlspecialchars_decode($this->context);
  }

  protected function prepareChangesContext(array $changes) {
    $contextArrays = [];

    foreach ($changes as $key => $change) {
      if ($change[0] == 1) {
        $this->collectBeforeChangeText($contextArrays, $key, $changes);

        $contextArrays[$key] = $change;

        $this->collectAfterChangeText($contextArrays, $key, $changes);
      }
    }
    ksort($contextArrays);

    return $contextArrays;
  }

  protected function getAddedChanges(array $changes) {
    $result = [];
    foreach ($changes as $change) {
      if ($change[0] == 1) {
        $result[] = $change[1];
      }
    }

    return $result;
  }

  protected function collectBeforeChangeText(array &$contextArrays, int $key, array $changes): void {
    $charPrevLeft = $this->numberCharSurrounding;
    for ($prev = $key - 1; $prev >=0; --$prev) {
      if ($charPrevLeft <= 0 || !isset($changes[$prev])) {
        break;
      }
      if (isset($contextArrays[$prev])) {
        if (!isset($contextArrays[$prev]['substring_taken'])) {
          break;
        }

        if (mb_strlen($changes[$prev][1]) >= ($contextArrays[$prev]['substring_taken'] + $charPrevLeft)) {
          $contextArrays[$prev][1] .= $this->getHtmlSubstring($changes[$prev][1], 0 - $charPrevLeft);
        } else {
          $contextArrays[$prev] = $changes[$prev];
        }
        break;
      }
      $contextArrays[$prev] = $changes[$prev];
      $contextArrays[$prev][1] = $this->getHtmlSubstring($contextArrays[$prev][1], 0 - $charPrevLeft);
      if (mb_strlen($contextArrays[$prev][1]) != mb_strlen($changes[$prev][1])) {
        $contextArrays[$prev][1] = $this->separator . $contextArrays[$prev][1];
        $contextArrays[$prev]['substring_taken'] = $charPrevLeft;
        break;
      } else {
        $charPrevLeft -= mb_strlen($changes[$prev][1]);
      }
    }
  }

  protected function collectAfterChangeText(array &$contextArrays, int $key, array $changes) {
    $charNextLeft = $this->numberCharSurrounding;
    for ($next = $key + 1; $next < count($changes); ++$next) {
      if ($charNextLeft <= 0 || !isset($changes[$next]) || isset($contextArrays[$next])) {
        break;
      }
      $contextArrays[$next] = $changes[$next];
      $contextArrays[$next][1] = $this->getHtmlSubstring($contextArrays[$next][1], 0, $charNextLeft);

      if (mb_strlen($contextArrays[$next][1]) != mb_strlen($changes[$next][1])) {
        $contextArrays[$next][1] .= $this->separator;
        $contextArrays[$next]['substring_taken'] = $charNextLeft;
        break;
      } else {
        $charNextLeft -= mb_strlen($changes[$next][1]);
      }
    }
  }

  private function getHtmlSubstring(string $html, int $from, int $too = 0) {
    if ($too <= 0) {
      $temp = mb_substr($html, $from);
      if (mb_stripos($html, '>') < mb_stripos($html, '<') || mb_stripos($html, '<') === FALSE) {
        $newPos = mb_strrpos($html, '<', $from);

        return mb_substr($html, 0 - $newPos);
      } else {
        return $temp;
      }
    } else {
      $temp = mb_substr($html, $from, $too);
      if (mb_strpos($temp, '<') > mb_strpos($temp, '>') ) {
        $newPos = mb_strpos($html, '<', $too);

        return mb_substr($html, $from, $newPos);
      } else {
        return $temp;
      }
    }
  }

}
