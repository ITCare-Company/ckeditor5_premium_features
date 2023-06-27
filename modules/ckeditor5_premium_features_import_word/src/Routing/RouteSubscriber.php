<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_import_word\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * {@inheritDoc}
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritDoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $cke5UploadImageRoute = $collection->get('ckeditor5.upload_image');
    $cke5UploadImageRoute->setDefaults([
      '_controller' => 'Drupal\ckeditor5_premium_features_import_word\Controller\CKEditor5ImportWordImageUploadController::upload',
    ]);
  }

}
