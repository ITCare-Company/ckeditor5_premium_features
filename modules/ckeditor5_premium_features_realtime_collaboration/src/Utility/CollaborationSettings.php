<?php

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Utility;

use Drupal\ckeditor5_premium_features\Utility\CommonCollaborationSettingsInterface;
use Drupal\ckeditor5_premium_features_realtime_collaboration\Form\SettingsForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Class for accessing collaboration config values.
 */
class CollaborationSettings implements CommonCollaborationSettingsInterface {

  private ImmutableConfig $collaborationSettings;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->collaborationSettings = $configFactory->get(SettingsForm::COLLABORATION_SETTINGS_ID);
  }

  /**
   * {@inheritdoc}
   */
  public function getAnnotationSidebarType(): string {
    return $this->collaborationSettings->get('sidebar') ?? 'auto';
  }

  /**
   * Returns mentions revision history on submit config.
   */
  public function isPresenceListEnabled(): bool {
    return (bool) ($this->collaborationSettings->get('presence_list') ?? TRUE);
  }

  /**
   * Returns mentions revision history on submit config.
   */
  public function getPresenceListCollapseAt(): int {
    return (int) ($this->collaborationSettings->get('presence_list_collapse_at') ?? 8);
  }

}
