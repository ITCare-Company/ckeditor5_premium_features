<?php

namespace Drupal\ckeditor5_premium_features\EventSubscriber;

use Drupal\ckeditor5_premium_features\CollaborationPermissions;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * CKEditor 5 Premium Features event subscriber.
 */
class ConfigSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a ConfigSubscriber object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManager
   *   The entity type manager
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Config save response event handler.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent
   *   Response event.
   */
  public function onConfigSave(ConfigCrudEvent $event) {
    $config = $event->getConfig();
    $isEditorConfig = strpos($config->getName(), 'editor.editor');
    if ($isEditorConfig !== 0 || $config->isNew()) {
      return;
    }
    $original = $config->getOriginal();
    $premiumPlugins = [
      'ckeditor5_premium_features_collaboration__comments',
      'ckeditor5_premium_features_collaboration__track_changes'
    ];

    // Exit if there was no collaboration plugins before.
    $originalEditorSettings = $original['settings'];
    $originalPlugins = $originalEditorSettings["plugins"] ? array_keys($originalEditorSettings["plugins"]) : [];
    if (empty(array_intersect($originalPlugins, $premiumPlugins))) {
      return;
    }

    // Revoke all collaboration permissions in case there is no collaboration
    // plugins after save.
    $editorSettings = $config->get('settings');
    $plugins = $editorSettings["plugins"] ? array_keys($editorSettings["plugins"]) : [];
    if (empty(array_intersect($plugins, $premiumPlugins))) {
      $formatId = $config->get('format');
      $formats = \Drupal::entityTypeManager()->getStorage('filter_format')->loadByProperties(['status' => TRUE]);
      if (!isset($formats[$formatId])) {
        return;
      }
      $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
      foreach ($roles as $role) {
        $permissions = CollaborationPermissions::PERMISSIONS;
        foreach ($permissions as $permission) {
          $permissionName = CollaborationPermissions::getPermissionName($formats[$formatId], $permission);
          if ($role->hasPermission($permissionName)) {
            $role->revokePermission($permissionName);
          }
        }
        $role->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ConfigEvents::SAVE => ['onConfigSave'],
    ];
  }

}
