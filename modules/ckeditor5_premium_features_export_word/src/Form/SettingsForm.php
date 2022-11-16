<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_word\Form;

use Drupal\ckeditor5_premium_features\Form\SharedBuildConfigFormBase;
use Drupal\ckeditor5_premium_features\Utility\FormElement;
use Drupal\Core\Config\Config;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the configuration form of the "Export to Word" feature.
 */
class SettingsForm extends SharedBuildConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckeditor5_premium_features_export_word.settings';
  }

  /**
   * {@inheritdoc}
   */
  public static function form(array $form, FormStateInterface $form_state, Config $config): array {
    $form['converter_url'] = [
      '#type' => 'textfield',
      '#title' => t('Converter URL'),
      '#description' => t('Leave this field empty unless you are using the on-premises version of Export to Word.'),
      '#default_value' => $config->get('converter_url'),
    ];

    $options_key = 'converter_options';
    $form[$options_key] = [
      '#type' => 'details',
      '#title' => t('Converter options'),
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
        '#title' => t("Margin $margin"),
        '#default_value' => $config->get($options_key . '.margin_' . $margin) ?? '1cm',
      ];
    }

    FormElement::pageOrientation($options, [
      '#default_value' => $config->get($options_key . '.page_orientation') ?? 'portrait',
    ]);

    $num_headers = $form_state->get('num_headers');
    $num_footers = $form_state->get('num_footers');

    if ($num_headers === NULL) {
      $headers_items = $config->get("$options_key.header") ?? [];
      $num_headers = count($headers_items);
      $form_state->set('num_headers', $num_headers);
    }
    if ($num_footers === NULL) {
      $footer_items = $config->get("$options_key.footer") ?? [];
      $num_footers = count($footer_items);
      $form_state->set('num_footers', $num_footers);
    }

    foreach (['header' => $num_headers, 'footer' => $num_footers] as $type => $type_count) {
      FormElement::headingFooter($options, $type, $config->get("$options_key.$type"), $type_count);
    }

    return $form;
  }

}
