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


    $form['mentions'] = [
      '#type' => 'fieldset',
      '#title' => t('Mentions/Annotations'),
    ];

    $form['mentions']['mention_min_character'] = [
      '#type' => 'number',
      '#title' => t('Minimal mention character.'),
      '#min' => 1,
      '#default_value' => $config->get('mention_min_character') ?? 1,
      '#description' => t('Set the number of letters after which the autocomplete panel will show up.'),
    ];
    $form['mentions']['mention_dropdown_limit'] = [
      '#type' => 'number',
      '#title' => t('Autocomplete list limit.'),
      '#min' => 1,
      '#default_value' => $config->get('mention_dropdown_limit') ?? 4,
      '#description' => t('The number of items displayed in the autocomplete list.'),
    ];
    $form['mentions']['mention_marker'] = [
      '#type' => 'textfield',
      '#title' => t('Annotation triggering character.'),
      '#min' => 1,
      '#default_value' => $config->get('mention_marker') ?? '#',
      '#description' => t('The character which triggers autocompletion for mention. It must be a single character.'),
    ];

    $form['revision_history'] = [
      '#type' => 'fieldset',
      '#title' => t('Revision History'),
    ];

    $form['revision_history']['add_revision_on_submit'] = [
      '#type' => 'checkbox',
      '#title' => t('Add revisions on form submit'),
      '#default_value' => $config->get('add_revision_on_submit') ?? TRUE,
      '#description' => t('If you leave this unchecked, new revisions will be saved only on demand.'),
    ];

    return $form;
  }

}
