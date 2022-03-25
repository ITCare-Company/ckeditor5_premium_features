<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Controller;

use Drupal\ckeditor5_premium_features_collaboration\Entity\SuggestionInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the suggestion entity controller.
 */
class SuggestionController extends ControllerBase {

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


  public function get(SuggestionInterface $ckeditor5_suggestion): JsonResponse {
    return new JsonResponse($ckeditor5_suggestion->toArray());
  }

  public function add(Request $request): JsonResponse {
    // @todo change request data to query paramters and fetch them by alphaNum etc.
    $data = $request->request;

    $object_data = [
      'id' => $data->getAlnum('id'),
      'uid' => $this->currentUser()->id(),
      'entity_type' => Html::decodeEntities(strip_tags((string) $data->get('type'))),
      'entity_id' => $data->getInt('article_id'),
      'data' => Json::encode($data->get('data')),
    ];

    $original_suggestion_id = $data->getAlnum('original_suggestion_id');
    if ($original_suggestion_id) {
      $original_suggestion = $this->suggestionStorage->load($original_suggestion_id);
      if ($original_suggestion instanceof SuggestionInterface) {
        $object_data = [
          'uid' => $original_suggestion->getAuthorId(),
          'created' => $original_suggestion->getCreatedTime(),
          'type' => $original_suggestion->getTargetEntityType(),
          'data' =>  $original_suggestion->getData(TRUE),
        ] + $object_data;
      }
    }

    $suggestion = $this->suggestionStorage->create($object_data);
    $suggestion->save();

    return new JsonResponse(['created' => $suggestion->getCreatedTime()]);
  }

  public function update(SuggestionInterface $ckeditor5_suggestion, Request $request): JsonResponse {
    $suggestion = $ckeditor5_suggestion;
    $data = $request->request;
    $has_comments = (bool) filter_var($data['has_comments'] ?? '', FILTER_VALIDATE_BOOL);

    $suggestion->setData((array) $data['data']);
    $suggestion->setCommentState($has_comments);

    return new JsonResponse();
  }

}
