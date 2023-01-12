<?php

namespace Drupal\ckeditor5_premium_features_mentions\Utility;

use Drupal\ckeditor5_premium_features_mentions\Form\SettingsForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Class for accessing collaboration config values.
 */
class MentionSettings {

  /**
   * Config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $collaborationSettings;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->collaborationSettings = $configFactory->get(SettingsForm::MENTION_SETTINGS_ID);
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
    return (int) ($this->collaborationSettings->get('mention_min_character') ?? 1);
  }

  /**
   * Returns mentions autocomplete list length config.
   */
  public function getMentionAutocompleteListLength(): int {

    return (int) ($this->collaborationSettings->get('mention_dropdown_limit') ?? 4);
  }

}
