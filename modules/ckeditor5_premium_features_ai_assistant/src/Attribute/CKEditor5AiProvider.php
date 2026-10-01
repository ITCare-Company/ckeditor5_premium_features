<?php

/*
 * Copyright (c) 2003-2026, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types = 1);

namespace Drupal\ckeditor5_premium_features_ai_assistant\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Defines ckeditor5_ai_provider attribute object.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class CKEditor5AiProvider extends Plugin {

  /**
   * Constructs a CKEditor5AiProvider attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param string $title
   *   The human-readable name of the plugin.
   * @param string $description
   *   The description of the plugin.
   * @param class-string|null $deriver
   *   (optional) The deriver class.
   */
  public function __construct(
    public readonly string $id,
    public readonly string $title,
    public readonly string $description,
    public readonly ?string $deriver = NULL,
  ) {}

}
