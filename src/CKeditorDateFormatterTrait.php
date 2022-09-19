<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features;

use Drupal\Core\Logger\LoggerChannelTrait;

trait CKeditorDateFormatterTrait {

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  public function format($time, $format = 'medium'): string {
    if (!$this->dateFormatter) {
      $this->dateFormatter = \Drupal::service('date.formatter');
    }

    return $this->dateFormatter->format($time, $format);
  }
}
