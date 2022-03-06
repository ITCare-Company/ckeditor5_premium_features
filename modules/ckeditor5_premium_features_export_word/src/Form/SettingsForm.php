<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_word\Form;

use Drupal\ckeditor5_premium_features\Utility\FormElement;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the configuration form of the "Export to Word" feature.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckeditor5_premium_features_export_word.settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      $this->getFormId(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config($this->getFormId());

    $form['coverter_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Converter URL'),
      '#description' => $this->t('Leave this field empty unless you are using the on-premises version of Export to Word.'),
      '#default_value' => $config->get('converter_url'),
    ];

    $options_key = 'converter_options';
    $form[$options_key] = [
      '#type' => 'details',
      '#title' => $this->t('Converter options'),
      '#tree' => TRUE,
      '#open' => TRUE,
    ];

    $options = &$form[$options_key];

    FormElement::format($options, [
      '#default_value' => $config->get($options_key . '.format') ?? 'A4',
    ]);

    $margins = [
      'top',
      'bottom',
      'left',
      'right',
    ];

    foreach ($margins as $margin) {
      $options['margin_' . $margin] = [
        '#type' => 'textfield',
        '#title' => $this->t("Margin $margin"),
        '#default_value' => $config->get($options_key . '.margin_' . $margin),
      ];
    }

    foreach (['header', 'footer'] as $type) {
      FormElement::headingFooter($options, $type, [
        [
          'html' => [
            '#default_value' => $config->get("$options_key.$type.0.html"),
          ],
          'css' => [
            '#default_value' => $config->get("$options_key.$type.0.css"),
          ],
          'type' => [
            '#default_value' => $config->get("$options_key.$type.0.type"),
          ],
        ],
      ]);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this
      ->config($this->getFormId())
      ->setData($form_state->cleanValues()->getValues())
      ->save();

    parent::submitForm($form, $form_state);
  }

}
