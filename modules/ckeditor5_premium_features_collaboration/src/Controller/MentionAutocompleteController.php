<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Controller;

use Drupal\ckeditor5_premium_features_collaboration\DataProvider\UserDataProvider;
use Drupal\ckeditor5_premium_features_collaboration\Utility\CollaborationSettings;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class MentionAutocompleteController extends ControllerBase {

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
  public function annotation() {
    $args = $this->requestStack->getCurrentRequest()->query;

    if (empty($args->get('query')) ) {
      return new JsonResponse([]);
    }

    /** @var \Drupal\user\Entity\User[] $matchedUsers */
    $matchedUsers = $this->userProvider->getPrivilegedEditors(
      $args->get('query'),
      $this->getDropdownLimit()
    );

    $resultList = [];
    foreach ($matchedUsers as $user) {
      $resultList[] = [
        'id' => $this->getMentionMarker() . $user->getDisplayName(),
        'link' => $user->toUrl(),
      ];
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
