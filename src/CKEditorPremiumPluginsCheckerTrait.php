<?php

/*
 * Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features;

/**
 * Trait for checking if in array of plugins are premium features.
 */
trait CKEditorPremiumPluginsCheckerTrait {

  /**
   * @param array $plugins
   * @return bool
   */
  private function hasPremiumFeaturesEnabled(array $config, $editor = FALSE): bool {
    $label = $editor ? $editor->getFilterFormat()->get('name') : '';
    if (isset($config['plugins']) && isset($config['plugins']['ckeditor5_premium_features_productivity_pack_base'])) {
      if (in_array(TRUE, $config['plugins']['ckeditor5_premium_features_productivity_pack_base'], TRUE)) {
        if ($label) {
          \Drupal::logger('ckeditor5_premium_features_ubb_debug')->info('%label Productivity pack is enabled. %plugin', ['%plugin' => print_r($plugin, TRUE), '%label' => $label]);
        }
        return TRUE;
      }
    }

    if (isset($config['toolbar']['items']) && array_intersect($this->getPremiumToolbarItems(), $config['toolbar']['items'])) {
      if ($label) {
        \Drupal::logger('ckeditor5_premium_features_ubb_debug')->info('%label Toolbar item without settings is enabled.', ['%label' => $label]);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns array of premium features toolbar items.
   *
   * @return array
   */
  private function getPremiumToolbarItems(): array {
    return [
      'aiAssistant',
      'aiCommands',
      'caseChange',
      'comment',
      'commentsArchive',
      'exportPdf',
      'exportWord',
      'formatPainter',
      'importWord',
      'insertTemplate',
      'multiLevelList',
      'revisionHistory',
      'tableOfContents',
      'trackChanges'
    ];
  }
}
