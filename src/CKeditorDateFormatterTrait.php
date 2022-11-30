<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features;

/**
 * Trait for formatting dates.
 */
trait CKeditorDateFormatterTrait {

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Formats time in the described format.
   *
   * @param string $time
   *   Time to be formatted.
   * @param string $format
   *   Format name.
   */
  public function format(string $time, string $format = 'medium'): string {
    if (!$this->dateFormatter) {
      $this->dateFormatter = \Drupal::service('date.formatter');
    }

    return $this->dateFormatter->format($time, $format);
  }

}
