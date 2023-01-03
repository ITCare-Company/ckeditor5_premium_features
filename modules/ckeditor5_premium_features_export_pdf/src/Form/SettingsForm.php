<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_pdf\Form;

use Drupal\ckeditor5_premium_features\Form\SharedBuildConfigFormBase;
use Drupal\ckeditor5_premium_features\Utility\FormElement;
use Drupal\Core\Config\Config;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the configuration form of the "Export to PDF" feature.
 */
class SettingsForm extends SharedBuildConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckeditor5_premium_features_export_pdf.settings';
  }

  /**
   * {@inheritdoc}
   */
  public static function getSettingsRouteName(): string {
    return 'ckeditor5_premium_features_export_pdf.form.settings';
  }

  /**
   * {@inheritdoc}
   */
  public static function form(array $form, FormStateInterface $form_state, Config $config): array {
    $form['converter_url'] = [
      '#type' => 'textfield',
      '#title' => t('Converter URL'),
      '#description' => t('Leave this field empty unless you are using the on-premises version of Export to PDF.'),
      '#default_value' => $config->get('converter_url'),
    ];

    $form['custom_css'] = [
      '#type' => 'textarea',
      '#title' => t('Custom css'),
      '#default_value' => $config->get('custom_css'),
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
      $margin_config = $config->get($options_key . '.margin_' . $margin);
      FormElement::marginElement($options, $margin, $margin_config);
    }

    FormElement::pageOrientation($options, [
      '#default_value' => $config->get($options_key . '.page_orientation') ?? 'portrait',
    ]);

    $options['header_html'] = [
      '#type' => 'textarea',
      '#title' => t('Header'),
      '#default_value' => $config->get($options_key . '.header_html'),
      '#ajax' => FALSE,
    ];

    $options['footer_html'] = [
      '#type' => 'textarea',
      '#title' => t('Footer'),
      '#default_value' => $config->get($options_key . '.footer_html'),
      '#ajax' => FALSE,
    ];

    $options['header_and_footer_css'] = [
      '#type' => 'textarea',
      '#title' => t('Header and footer css'),
      '#default_value' => $config->get($options_key . '.header_and_footer_css'),
      '#ajax' => FALSE,
    ];

    return $form;
  }

}
