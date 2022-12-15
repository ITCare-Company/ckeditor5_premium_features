<?php

namespace Drupal\ckeditor5_premium_features\Utility;

/**
 * Interface describing common collaboration settings methods.
 */
interface CommonCollaborationSettingsInterface {

  /**
   * Returns annotation sidebar type config.
   */
  public function getAnnotationSidebarType(): string;

}
