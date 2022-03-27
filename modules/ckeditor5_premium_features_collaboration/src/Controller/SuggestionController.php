<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Controller;

use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\ckeditor5_premium_features_collaboration\Utility\ExceptionResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Entity\EntityStorageException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\InputBag;
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
    $has_original = $original_suggestion instanceof SuggestionInterface;

    $callback = $has_original ? 'getSuggestionEntityData' : 'getSuggestionRequestData';
    $source = $has_original ? $original_suggestion : $data;

    [
      $object_data,
      $suggestion_data,
      $attributes,
      $type,
    ] = call_user_func([__CLASS__, $callback], $object_data, $source);

    /** @var \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $suggestion */
    $suggestion = $this->suggestionStorage->create($object_data);
    $suggestion->setEntityTypeTargetId($data->get('entity_type', ''));
    $suggestion->setType($type);
    $suggestion->setData($suggestion_data);
    $suggestion->setAttributes($attributes);

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
    $has_comments = $request->request->getBoolean('has_comments');

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

  /**
   * Gets the suggestion data based on what was send with the request.
   *
   * @param array $current_data
   *   The common data already added.
   * @param \Symfony\Component\HttpFoundation\InputBag $data
   *   The request paramters.
   *
   * @return array
   *   The data required to be stored on the entity.
   */
  protected function getSuggestionRequestData(array $current_data, InputBag $data): array {
    $object_data = [
      'uid' => $this->currentUser()->id(),
    ] + $current_data;

    return [
      $object_data,
      (string) $data->get('data'),
      (string) $data->get('attributes'),
      (string) $data->get('type'),
    ];
  }

  /**
   * Gets the suggestion data based on the suggestion entity..
   *
   * @param array $current_data
   *   The common data already added.
   * @param \Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface $suggestion
   *   The suggestion entity.
   *
   * @return array
   *   The data required to be stored on the entity.
   */
  protected function getSuggestionEntityData(array $current_data, SuggestionInterface $suggestion): array {
    $object_data = [
      'uid' => $suggestion->getAuthorId(),
      'type' => $suggestion->getType(),
      'created' => $suggestion->getCreatedTime(),
    ] + $current_data;

    return [
      $object_data,
      $suggestion->getData(),
      $suggestion->getAttributes(),
      $suggestion->getType(),
    ];
  }

}
