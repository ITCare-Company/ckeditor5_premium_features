<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Utility;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * CollaborationModuleIntegrator class.
 */
class CollaborationModuleIntegrator {

  /**
   * @var bool
   */
  private bool $isNonRtcModuleInstalled;

  /**
   * @var bool
   */
  private bool $isRtcModuleInstalled;

  /**
   *
   */
  public function __construct(protected ModuleHandlerInterface $moduleHandler) {
    $this->isNonRtcModuleInstalled = $moduleHandler->moduleExists('ckeditor5_premium_features_collaboration');
    $this->isRtcModuleInstalled = $moduleHandler->moduleExists('ckeditor5_premium_features_realtime_collaboration');
  }

  /**
   * Checks if non rtc module is enabled.
   */
  public function isNonRtcEnabled(): bool {
    return $this->isNonRtcModuleInstalled;
  }

  /**
   * Checks if rtc module is enabled.
   */
  public function isRtcEnabled(): bool {
    return $this->isRtcModuleInstalled;
  }

}
