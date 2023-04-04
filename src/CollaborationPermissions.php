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

  public const COMMENTS_WRITE = 'comments_write';
  public const COMMENTS_ADMIN = 'comments_admin';
  public const DOCUMENT_SUGGESTIONS = 'document_suggestions';
  public const DOCUMENT_WRITE = 'document_write';

  public const PERMISSIONS = [
    self::COMMENTS_WRITE,
    self::COMMENTS_ADMIN,
    self::DOCUMENT_SUGGESTIONS,
    self::DOCUMENT_WRITE,
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
          $permissions[$formatPermission . ' with collaboration ' . $collaborationPermission] = [
            'title' => $this->t('Collaboration @permission for the <a href=":url">@format</a> text format',
              [
                ':url' => $format->toUrl()->toString(),
                '@format' => $format->label(),
                '@permission' => $this->getPermissionLabel($collaborationPermission),
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
     * Returns label of the collaboration permission.
     *
     * @param string $permission
     *   Collaboration permission name.
     *
     * @return string|TranslatableMarkup
     *   Permission label
     */
  private function getPermissionLabel(string $permission): string|TranslatableMarkup {
    return match ($permission) {
      self::COMMENTS_WRITE => $this->t('Write comments'),
      self::COMMENTS_ADMIN => $this->t('Administer comments'),
      self::DOCUMENT_SUGGESTIONS => $this->t('Add suggestions'),
      self::DOCUMENT_WRITE => $this->t('Evaluate suggestions and edit content'),
      default => '',
    };
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
      self::COMMENTS_WRITE => $this->t('Allows to add and delete own collaboration comments'),
      self::COMMENTS_ADMIN => $this->t('Allows to add and delete all collaboration comments'),
      self::DOCUMENT_SUGGESTIONS => $this->t('Allows to add and edit suggestions only. Disallows to make non-suggestion changes'),
      self::DOCUMENT_WRITE => $this->t('Allows to evaluate suggestions and make non-suggestion changes.'),
      default => '',
    };
  }

}
