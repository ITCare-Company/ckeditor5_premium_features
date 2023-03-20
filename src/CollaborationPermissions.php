<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of the filter module.
 */
class CollaborationPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  public const ADMIN = 'admin';
  public const EDIT = 'edit';
  public const SUGGESTIONS_ONLY = 'suggestions_only';
  public const COMMENTS_ONLY = 'comments_only';
  public const READ_ONLY = 'read_only';

  public const PERMISSIONS = [
    self::ADMIN,
    self::EDIT,
    self::SUGGESTIONS_ONLY,
    self::COMMENTS_ONLY,
  ];

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new FilterPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns an array of filter permissions.
   *
   * @return array
   */
  public function permissions(): array {
    $permissions = [];
    /** @var \Drupal\filter\FilterFormatInterface[] $formats */
    $formats = $this->entityTypeManager->getStorage('filter_format')->loadByProperties(['status' => TRUE]);
    uasort($formats, 'Drupal\Core\Config\Entity\ConfigEntityBase::sort');
    foreach ($formats as $format) {
      if ($formatPermission = $format->getPermissionName()) {
        foreach (self::PERMISSIONS as $collaborationPermission) {
          $description = $this->getPermissionDescription($collaborationPermission);
          $permissions[$formatPermission . '_' . $collaborationPermission] = [
            'title' => $this->t('Collaboration "@permission" permission for the <a href=":url">@label</a> text format',
              [
                ':url' => $format->toUrl()->toString(),
                '@label' => $format->label(),
                '@permission' => $collaborationPermission,
              ]
            ),
            'description' => [
              '#prefix' => '<em>',
              '#markup' => $description,
              '#suffix' => '</em>',
            ],
            // This permission is generated on behalf of $format text format,
            // therefore add this text format as a config dependency.
            'dependencies' => [
              $format->getConfigDependencyKey() => [
                $format->getConfigDependencyName(),
              ],
            ],
          ];
        }
      }
    }
    return $permissions;
  }

  /**
   * Returns description for the collaboration permission.
   *
   * @param string $permission
   *   Collaboration permission name.
   *
   * @return string|TranslatableMarkup
   *   Permission description
   */
  private function getPermissionDescription(string $permission): string|TranslatableMarkup {
    return match ($permission) {
      self::ADMIN => $this->t('Collaboration admin'),
      self::EDIT => $this->t('Collaboration editor. User can edit document'),
      self::SUGGESTIONS_ONLY => $this->t('User is able to add only suggestions to the document'),
      self::COMMENTS_ONLY => $this->t('User is able to add only comments to the document'),
      default => '',
    };
  }

}
