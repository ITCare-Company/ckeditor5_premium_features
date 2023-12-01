<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types = 1);

namespace Drupal\ckeditor5_premium_features_ai_assistant;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface for ckeditor5_ai_provider plugins.
 */
interface CKEditor5AiProviderInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

  /**
   * Handle completions request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The completions response.
   */
  public function processRequest(Request $request): Response;

  /**
   * Returns array of field configuration to build in AI Settings form.
   *
   * Fields should have structure of Form API fields.
   * [
   *  "example_field" => ['#type' => 'textfield', '#title' => 'Sample title']
   * ]
   *
   * @return array
   *   Array of fields.
   */
  public function getConfigFields(): array;

}
