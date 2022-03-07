<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Generator;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the common form elements that may be reused among the features.
 */
class FileNameFromAlias implements FileNameGeneratorInterface {

  /**
   * The alias manager that caches alias lookups based on the request.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected AliasManagerInterface $aliasManager;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected CurrentPathStack $currentPath;

  /**
   * Constructs a new WorkspaceRequestSubscriber instance.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The alias manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   */
  public function __construct(AliasManagerInterface $alias_manager, CurrentPathStack $current_path) {
    $this->aliasManager = $alias_manager;
    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path_alias.manager'),
      $container->get('path.current'),
    );
  }

  /**
   * Generate file name based on node alias.
   */
  public function generate(): string {
    $node = \Drupal::routeMatch()->getParameter('node') ?? NULL;
    $alias = $node->toUrl()->toString() ?? NULL;
    if ($alias) {
      $alias = ltrim($alias, '/');
      return str_replace('/', '-', $alias);
    }
    return 'filename';
  }

}
