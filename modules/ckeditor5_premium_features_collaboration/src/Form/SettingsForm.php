<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_collaboration\Form;

use Drupal\ckeditor5_premium_features\Form\SharedBuildConfigFormBase;
use Drupal\Core\Config\Config;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the configuration form of the "Collaboration" feature.
 */
class SettingsForm extends SharedBuildConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckeditor5_premium_features_collaboration.settings';
  }

  /**
   * {@inheritdoc}
   */
  public static function form(array $form, FormStateInterface $form_state, Config $config): array {
    $form['sidebar'] = [
      '#type' => 'select',
      '#title' => t('Annotation sidebar'),
      '#options' => [
        'auto' => t('Automatic'),
        'inline' => t('Use inline balloons'),
        'narrowSidebar' => t('Use narrow sidebar'),
        'wideSidebar' => t('Use wide sidebar'),
      ],
      '#default_value' => $config->get('sidebar') ?? 'auto',
    ];

    $form['revision_history'] = [
      '#type' => 'fieldset',
      '#title' => t('Revision History'),
    ];

    $form['revision_history']['add_revision_on_submit'] = [
      '#type' => 'checkbox',
      '#title' => t('Add revisions on form submit'),
      '#default_value' => $config->get('add_revision_on_submit') ?? FALSE,
      '#description' => t('If you leave this unchecked, new revisions will be saved only on demand.'),
    ];

    return $form;
  }

}
