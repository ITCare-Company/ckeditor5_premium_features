<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_realtime_collaboration\Plugin\CKEditor5Plugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * CKEditor 5 Track changes plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class TrackChanges extends Realtime {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {

    return [
      'is_turn_on' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $note = $this->t('In order to setup the Real Time Collaboration, use the <a href="@url">global realtime collaboration configuration instead</a>.', [
      '@url' => Url::fromRoute('ckeditor5_premium_features_realtime_collaboration.form.settings')->toString(),
    ]);
    $form['note'] = [
      ['#markup' => '<p>' . $note . '</p>'],
    ];

    $form['is_turn_on'] = [
      '#type' => 'checkbox',
      '#title' => t('Turn on track changes'),
      '#default_value' => $this->configuration['is_turn_on'] ?? FALSE,
      '#description' => t('If checked, track changes will be turned on after editor initialization.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $formValues = $form_state->getValues();
    $isTurnedOn = $formValues['is_turn_on'] ?? FALSE;
    $this->configuration['is_turn_on'] = (bool) $isTurnedOn;
  }

}
