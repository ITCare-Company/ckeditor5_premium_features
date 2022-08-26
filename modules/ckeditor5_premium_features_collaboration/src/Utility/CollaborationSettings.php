<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Utility;

use Drupal\ckeditor5_premium_features_collaboration\Form\SettingsForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Class for accessing collaboration config values.
 */
class CollaborationSettings {

  private ImmutableConfig $collaborationSettings;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->collaborationSettings = $configFactory->get(SettingsForm::COLLABORATION_SETTINGS_ID);
  }

  /**
   * Returns mentions marker config.
   */
  public function getMentionsMarker(): string {
    return $this->collaborationSettings->get('mention_marker') ?? '#';
  }

  /**
   * Returns mentions minimal character count config.
   */
  public function getMentionMinimalCharactersCount(): int {
    return (int) $this->collaborationSettings->get('mention_min_character') ?? 1;
  }

  /**
   * Returns mentions autocomplete list length config.
   */
  public function getMentionAutocompleteListLength(): int {
    return (int) $this->collaborationSettings->get('mention_min_character') ?? 5;
  }

  /**
   * Returns annotation sidebar type config.
   */
  public function getAnnotationSidebarType(): string {
    return $this->collaborationSettings->get('sidebar');
  }

  /**
   * Returns mentions revision history on submit config.
   */
  public function isRevisionHistoryOnSubmit(): bool {
    return (bool) $this->collaborationSettings->get('add_revision_on_submit');
  }

}
