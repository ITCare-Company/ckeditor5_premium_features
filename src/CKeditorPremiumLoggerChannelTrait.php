<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features;

use Drupal\Core\Logger\LoggerChannelTrait;

/**
 * Trait providing error logging helper methods.
 */
trait CKeditorPremiumLoggerChannelTrait {
  use LoggerChannelTrait;

  /**
   * Log error message.
   *
   * @param string $message
   *   Message.
   * @param array $prams
   *   Parameters.
   */
  protected function error(string $message, array $prams): void {
    $this->getLogger(self::getLoggerName())->error($message, $prams);
  }

  /**
   * Returns the logger name.
   */
  public static function getLoggerName(): string {
    return 'ckeditor5_premium_features';
  }

}
