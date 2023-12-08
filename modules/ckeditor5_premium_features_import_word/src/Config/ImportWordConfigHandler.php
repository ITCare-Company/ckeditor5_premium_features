<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_import_word\Config;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Helper for Import from Word configuration.
 */
class ImportWordConfigHandler implements ImportWordConfigHandlerInterface {

  /**
   * The configuration object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $config;

  /**
   * Constructs the handler.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   */
  public function __construct(protected ConfigFactoryInterface $configFactory) {
    $this->config = $this->configFactory->get('ckeditor5_premium_features_import_word.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function isWordStylesEnabled(): bool {
    return (bool) $this->config->get('word_styles');
  }

  /**
   * {@inheritdoc}
   */
  public function isStylesDisabled(): bool {
    return (bool) $this->config->get('disable_styles');
  }

  /**
   * {@inheritdoc}
   */
  public function isCssResetsEnabled(): bool {
    return (bool) $this->config->get('css_resets');
  }

  /**
   * {@inheritdoc}
   */
  public function getCommentsStyles(): string {
    return $this->config->get('comments_styles') ?? 'basic';
  }

}
