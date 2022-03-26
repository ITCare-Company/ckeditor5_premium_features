<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Controller;

use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Utility\ExceptionResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityStorageException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the suggestion entity controller.
 */
class SuggestionController extends ControllerBase {

  /**
   * Constructs the Suggestion controller instance.
   *
   * @param \Drupal\Core\Entity\ContentEntityStorageInterface $suggestionStorage
   *   The suggestion entity storage.
   */
  public function __construct(
    protected ContentEntityStorageInterface $suggestionStorage,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('entity_type.manager')->getStorage(SuggestionInterface::ENTITY_TYPE_ID),
    );
  }

  /**
   * Respond to the GET request with the CKEDitor5 Suggestion entity data.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $ckeditor5_suggestion
   *   The suggestion entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The suggestion data.
   */
  public function get(SuggestionInterface $ckeditor5_suggestion): JsonResponse {
    // @todo We can replace it with a CacheableJsonResponse if possible.
    return new JsonResponse($ckeditor5_suggestion->toArray());
  }

  /**
   * Creates the CKEDitor5 Suggestion entity.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The created time in case of success or error.
   */
  public function add(Request $request): JsonResponse {
    $data = $request->request;

    $object_data = [
      'id' => $data->getAlnum('id'),
      'entity_id' => $data->getInt('entity_id'),
    ];

    $original_suggestion = $this->loadOriginalSuggestionFromRequestData($request);
    if ($original_suggestion instanceof SuggestionInterface) {
      $object_data = [
        'uid' => $original_suggestion->getAuthorId(),
        'entity_type' => $original_suggestion->getEntityTypeTargetId(),
        'created' => $original_suggestion->getCreatedTime(),
      ] + $object_data;

      $entity_type = $original_suggestion->getEntityTypeTargetId();
      $suggestion_data = $original_suggestion->getData();
    }
    else {
      $object_data = [
        'uid' => $this->currentUser()->id(),
      ] + $object_data;

      $entity_type = $data->get('entity_type');
      $suggestion_data = (string) $data->get('data');
    }

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $suggestion */
    $suggestion = $this->suggestionStorage->create($object_data);
    $suggestion->setData($suggestion_data);
    $suggestion->setEntityTypeTargetId($entity_type);

    try {
      $suggestion->save();

      return new JsonResponse(['created' => $suggestion->getCreatedTime()]);
    }
    catch (EntityStorageException $exception) {
      return ExceptionResponse::entityStorage($exception);
    }
  }

  /**
   * Updates the CKEDitor5 Suggestion entity.
   *
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $ckeditor5_suggestion
   *   The suggestion entity.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The success message or error.
   */
  public function update(SuggestionInterface $ckeditor5_suggestion, Request $request): JsonResponse {
    $suggestion = $ckeditor5_suggestion;
    $data = $request->request;
    $has_comments = (bool) filter_var($data['has_comments'] ?? '', FILTER_VALIDATE_BOOL);

    $suggestion->setData((array) $data['data']);
    $suggestion->setCommentState($has_comments);

    try {
      $suggestion->save();

      return new JsonResponse(['ok' => TRUE]);
    }
    catch (EntityStorageException $exception) {
      return ExceptionResponse::entityStorage($exception);
    }
  }

  /**
   * Loads the original suggestion if present in the request data.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface|null
   *   The suggestion entity or null.
   */
  protected function loadOriginalSuggestionFromRequestData(Request $request): ?SuggestionInterface {
    $original_suggestion_id = $request->request->getAlnum('original');

    return $original_suggestion_id ? $this->suggestionStorage->load($original_suggestion_id) : NULL;
  }

}
