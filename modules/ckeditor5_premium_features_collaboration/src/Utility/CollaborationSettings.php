<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Utility;

use Drupal\ckeditor5_premium_features_collaboration\Form\SettingsForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

class CollaborationSettings {

  private ImmutableConfig $notificationSettings;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->notificationSettings = $configFactory->get(SettingsForm::COLLABORATION_SETTINGS_ID);
  }

  public function getMentionsMarker(): string {
    return $this->notificationSettings->get('mention_marker');
  }

  public function getMentionMinimalCharactersCount(): int {
    return (int) $this->notificationSettings->get('mention_min_character');
  }

  public function getMentionAutocompleteListLength(): int {
    return (int) $this->notificationSettings->get('mention_min_character');
  }

  public function getAnnotationSidebarType(): string {
    return $this->notificationSettings->get('sidebar');
  }

  public function isRevisionHistoryOnSubmit(): bool {
    return (bool) $this->notificationSettings->get('add_revision_on_submit');
  }

}
