<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Generator;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the common form elements that may be reused among the features.
 */
class FileNameFromAlias implements FileNameGeneratorInterface {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * Constructs a new BookNavigationCacheContext service.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(RouteMatchInterface $routeMatch) {
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('route_match'),
    );
  }

  /**
   * Generate file name based on node alias.
   */
  public function generate(): string {
    $node = $this->routeMatch->getParameter('node') ?? NULL;
    $alias = $node->toUrl()->toString() ?? NULL;
    if ($alias) {
      $alias = ltrim($alias, '/');
      return str_replace('/', '-', $alias);
    }
    return 'filename';
  }

}
