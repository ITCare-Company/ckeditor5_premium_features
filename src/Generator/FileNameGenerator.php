<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Generator;

use Drupal\Component\Utility\Html;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides file name generator based on current node alias.
 */
class FileNameGenerator implements FileNameGeneratorInterface {

  public const DEFAULT_FILENAME = 'filename';

  /**
   * Constructs a new BookNavigationCacheContext service.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(
    protected RouteMatchInterface $routeMatch
  ) {
  }

  /**
   * Generate file name based entity alias.
   */
  public function generateFromRequest(): string {
    $route_name = $this->routeMatch->getRouteName();
    $route_param = explode('.', $route_name);
    $entity = $this?->routeMatch->getParameter($route_param[1]);
    if ($entity) {
      $alias = $entity->toUrl()->toString();
      return $this->convertUrlToFileName($alias);
    }

    return self::DEFAULT_FILENAME;
  }

  /**
   * Cleanup and convert alias to friendly filename.
   *
   * @param string $alias
   *   Entity alias/url.
   *
   * @return string
   *   Converted filename.
   */
  public function convertUrlToFileName(string $alias): string {
    $alias = ltrim($alias, '/');

    return Html::cleanCssIdentifier($alias);
  }

}
