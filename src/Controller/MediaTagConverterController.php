<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Controller;

use Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider;
use Drupal\ckeditor5_premium_features_collaboration\Utility\CollaborationSettings;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class MediaTagConverterController extends ControllerBase {

  /**
   * @param \Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider $userProvider
   * @param \Drupal\ckeditor5_premium_features_collaboration\Utility\CollaborationSettings $collaborationSettings
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   */
  public function __construct(
    protected UserDataProvider $userProvider,
    protected CollaborationSettings $collaborationSettings,
    protected RequestStack $requestStack
  ) {
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ckeditor5_premium_features_collaboration.data_provider.users'),
      $container->get('ckeditor5_premium_features_collaboration.collaboration_settings'),
      $container->get('request_stack')
    );
  }

  /**
   * Method returning a json response with users matching query criteria.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|\Symfony\Component\HttpFoundation\JsonResponse
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function decodeMediaTags() {
    $args = $this->requestStack->getCurrentRequest()->request;

    if (empty($args->get('media')) ) {
      return new JsonResponse([]);
    }

    $media = Json::decode($args->get('media'));

    $entityTypes = [];

    foreach ($media as $entityInfo) {
      $entityTypes[$entityInfo['type']][] = $entityInfo['id'];
    }

    $resultList = [];
    foreach ($entityTypes as $type => $ids) {
      $entities = \Drupal::entityTypeManager()->getStorage($type)->loadByProperties([
        'uuid' => $ids
      ]);
      foreach ($entities as $entity) {
        // get the file source value for the media entity
        $source_value = $entity->getSource()->getSourceFieldValue($entity);

        // if a file resource exists, then build an image_style url based off of it using the image_style 'thumbnail'
        if ($source_value) {
          $file_entity = File::load($source_value);
          $resultList[$entity->uuid()] = ImageStyle::load('thumbnail')->buildUrl($file_entity->getFileUri());
        }
      }
    }

    return new AjaxResponse($resultList);
  }

  /**
   * Returns a marker character used for starting annotations.
   */
  protected function getMentionMarker(): string {
    return $this->collaborationSettings->getMentionsMarker();
  }

  /**
   * Returns the maximum number of suggestions displayed.
   */
  protected function getDropdownLimit(): int {
    return $this->collaborationSettings->getMentionAutocompleteListLength();
  }

}
