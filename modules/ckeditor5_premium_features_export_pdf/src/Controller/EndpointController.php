<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_pdf\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the controller for endpoints required by the pdf export feature.
 */
class EndpointController extends \Drupal\ckeditor5_premium_features\Controller\EndpointController {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('ckeditor5_premium_features_export_pdf.token_generator'),
    );
  }

}
