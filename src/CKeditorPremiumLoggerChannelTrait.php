<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features;

use Drupal\Core\Logger\LoggerChannelTrait;

trait CKeditorPremiumLoggerChannelTrait {
  use LoggerChannelTrait;

  protected function error($message, array $prams): void {
    $this->getLogger(self::getLoggerName())->error($message, $prams);
  }

  public static function getLoggerName(): string {
    return 'ckeditor5_premium_features';
  }
}
