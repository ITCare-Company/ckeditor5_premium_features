<?php

/*
 * Copyright (c) 2003-2024, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_wproofreader\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the configuration form of the "WProofreader" feature.
 */
class SettingsForm extends ConfigFormBase {

  const WPROOFREADER_SETTINGS_ID = 'ckeditor5_premium_features_wproofreader.settings';
  const DEFAULT_WSCBUNDLE_URL = 'https://svc.webspellchecker.net/spellcheck31/wscbundle/wscbundle.js';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckeditor5_premium_features_ai_assistant_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      self::WPROOFREADER_SETTINGS_ID,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config(self::WPROOFREADER_SETTINGS_ID);

    $form['src_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('WebSpellChecker bundle URL'),
      '#description' => $this->t('The URL to the custom proxy endpoint.'),
      '#default_value' => $config->get('src_url') ?? self::DEFAULT_WSCBUNDLE_URL,
      '#required' => TRUE,
    ];

    $form['service_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service ID'),
      '#description' => $this->t('A special service ID value (activation key) that is used for the service activation'),
      '#default_value' => $config->get('service_id') ?? '',
      '#states' => [
        'required' => [
          ':input[name="server_based_version"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $documentationUrl = 'https://webspellchecker.com/docs/api/wscbundle/Options.html';

    $form['lang_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Language code'),
      '#description' => $this->t('See the <a href="@doc_url" target="_blank">documentation</a> for the list of available languages. If value is not provided it will be set to the "auto".', ['@doc_url' => $documentationUrl]),
      '#default_value' => $config->get('lang_code') ?? 'auto',
    ];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced settings'),
      '#description' => $this->t('See the <a href="@doc_url" target="_blank">documentation</a> for more details about configuration fields.', ['@doc_url' => $documentationUrl]),
      '#open' => FALSE,
    ];

    $form['advanced']['default_api'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use default WebSpellChecker API Endpoint.'),
      '#description' => $this->t('<b>Note: Your Service ID will be visible in the editor configuration.</b>'),
      '#default_value' => $config->get('default_api') ?? '',
    ];

    $form['advanced']['server_based_version'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use Server-based version of the WProofreader'),
    ];
    $form['advanced']['on_premises_container'] = [
      '#type' => 'container',
      '#states' => [
        'disabled' => [
          ':input[name="server_based_version"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $onPremisesStates = [
      'required' => [
        ':input[name="server_based_version"]' => ['checked' => TRUE],
      ],
    ];

    $form['advanced']['on_premises_container']['service_protocol'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service Protocol'),
      '#description' => $this->t('A protocol which is used to access the service.'),
      '#default_value' => $config->get('service_protocol') ?? '',
      '#attributes' => [
        'placeholder' => 'https',
      ],
      '#states' => $onPremisesStates,
    ];
    $form['advanced']['on_premises_container']['service_host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service Host'),
      '#description' => $this->t('A host name of the service.'),
      '#default_value' => $config->get('service_host') ?? '',
      '#attributes' => [
        'placeholder' => 'localhost',
      ],
      '#states' => $onPremisesStates,
    ];
    $form['advanced']['on_premises_container']['service_port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service Port'),
      '#description' => $this->t('A default port of the service.'),
      '#default_value' => $config->get('service_port') ?? '',
      '#attributes' => [
        'placeholder' => '443',
      ],
      '#states' => $onPremisesStates,
    ];
    $form['advanced']['on_premises_container']['service_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service Path'),
      '#description' => $this->t('A path to the service.'),
      '#default_value' => $config->get('service_path') ?? '',
      '#attributes' => [
        'placeholder' => 'virtual_directory/api',
      ],
      '#states' => $onPremisesStates,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(self::WPROOFREADER_SETTINGS_ID)
      ->setData($form_state->cleanValues()->getValues())
      ->save();
    parent::submitForm($form, $form_state);
  }

}
