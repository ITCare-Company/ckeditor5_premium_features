<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Form;

use Drupal\ckeditor5_premium_features\Form\SharedBuildConfigFormBase;
use Drupal\Core\Config\Config;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the configuration form of the "Realtime collaboration" feature.
 */
class SettingsForm extends SharedBuildConfigFormBase {

  /**
   * {@inheritdoc}
   */
  final public function getFormId(): string {
    return 'ckeditor5_premium_features_realtime_collaboration.settings';
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

    $form['presence_list'] = [
      '#type' => 'checkbox',
      '#title' => t('Presence list'),
      '#default_value' => $config->get('presence_list') ?? TRUE,
    ];

    $form['presence_list_collapse_at'] = [
      '#type' => 'number',
      '#title' => t('Presence list collapse items'),
      '#default_value' => $config->get('presence_list_collapse_at') ?? 8,
    ];

    return $form;
  }

}
