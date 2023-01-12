<?php

namespace Drupal\ckeditor5_premium_features_collaboration\Utility;

use Drupal\ckeditor5_premium_features\Utility\CommonCollaborationSettingsInterface;
use Drupal\ckeditor5_premium_features_collaboration\Form\SettingsForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Class for accessing collaboration config values.
 */
class CollaborationSettings implements CommonCollaborationSettingsInterface {

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
    $this->collaborationSettings = $configFactory->get(SettingsForm::COLLABORATION_SETTINGS_ID);
  }

  /**
   * {@inheritdoc}
   */
  public function getAnnotationSidebarType(): string {
    return $this->collaborationSettings->get('sidebar') ?? 'auto';
  }

  /**
   * Returns revision history on submit config.
   */
  public function isRevisionHistoryOnSubmit(): bool {
    return (bool) ($this->collaborationSettings->get('add_revision_on_submit') ?? TRUE);
  }

}
