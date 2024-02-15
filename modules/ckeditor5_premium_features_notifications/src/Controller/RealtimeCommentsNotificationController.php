<?php

/*
 * Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_notifications\Controller;

use Drupal\ckeditor5_premium_features_realtime_collaboration\Utility\NotificationIntegrator;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handles the instant realtime notification after comment is submitted.
 *
 * @internal
 *   Controller classes are internal.
 */
class RealtimeCommentsNotificationController extends ControllerBase {

  /**
   * Constructs a new RealtimeCommentsNotificationController.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   */
  public function __construct(protected RequestStack $requestStack, protected NotificationIntegrator $notificationIntegrator) {
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('ckeditor5_premium_features_realtime_collaboration.notification_integrator')
    );
  }

  /**
   * Passes the comment data for further processing.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON object including the media uuid or error message.
   */
  public function send(Request $request): JsonResponse {
    $postData = $request->getContent();

    if (!$postData) {
      return new JsonResponse(null, 400);
    }

    $data = Json::decode($postData);

    $this->notificationIntegrator->handleInstantCommentNotification($data);

    return new JsonResponse("success", 200);
  }

  /**
   * Access handler.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Access result for current user.
   */
  public function access(AccountInterface $account): AccessResult {
    $channel = $this->requestStack->getCurrentRequest()?->attributes->get('channel');
    if (!$channel) {
      return AccessResult::forbidden("Missing channel argument.");
    }

    $entities = $this->entityTypeManager()->getStorage($channel->get('entity_type')->value)->loadByProperties(['uuid' => $channel->get('entity_id')->value]);
    if (empty($entities)) {
      return AccessResult::forbidden("Target entity does not exist.");
    }

    $entity = reset($entities);

    return AccessResult::allowedIf($entity->access('update', $account));
  }

}
