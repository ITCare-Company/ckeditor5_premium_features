<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_import_word\Controller;

use Drupal\ckeditor5_premium_features\CKeditorPremiumLoggerChannelTrait;
use Drupal\ckeditor5_premium_features_import_word\Utility\ImportWordMediaUploader;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns response for Import Word media upload.
 *
 * @internal
 *   Controller classes are internal.
 */
class CKEditor5ImportWordMediaUploaderController extends ControllerBase {

  use CKeditorPremiumLoggerChannelTrait;

  /**
   * Constructs a new CKEditor5ImportWordMediaUploaderController.
   *
   * @param \Drupal\ckeditor5_premium_features_import_word\Utility\ImportWordMediaUploader $importWordMediaUploader
   *   Import Word media uploader.
   */
  public function __construct(protected ImportWordMediaUploader $importWordMediaUploader,) {
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckeditor5_premium_features_import_word.media_uploader'),
    );
  }

  /**
   * Uploads image and creates media entity..
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON object including the media uuid or error message.
   */
  public function upload(Request $request) {
    $editor = $request->attributes->get('editor');
    $base64Data = $request->request->get('image');
    if (!$base64Data) {
      return new JsonResponse(['error' => 'Wrong base64 data provided'], 400);
    }
    try {
      $mediaUuid = $this->importWordMediaUploader->createMedia($editor, $base64Data);
    }
    catch (\Exception $exception) {
      $this->logException('An error occurred while creating media entity from Word document', $exception);
      return new JsonResponse(['error' => 'Something went wrong'], 400);
    }

    return new JsonResponse(['mediaUuid' => $mediaUuid], 200);
  }

}
