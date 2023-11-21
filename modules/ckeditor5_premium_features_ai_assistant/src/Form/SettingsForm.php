<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_ai_assistant\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the configuration form of the "AI Assistant" feature.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ckeditor5_premium_features_ai_assistant.settings';
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
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config($this->getFormId());

    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api Url'),
      '#description' => $this->t('Provide the URL to the OpenAI proxy endpoint in your application..'),
      '#default_value' => $config->get('api_url'),
    ];

    $form['auth_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Auth key'),
      '#required' => FALSE,
      '#description' => $this->t('Use your API key ONLY in a development environment or for testing purposes!.'),
      '#default_value' => $config->get('auth_key'),
    ];

    $form['proxy_auth_key'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use <b>Auth key</b> field as an endpoint to receive authorization key for your proxy'),
      '#required' => FALSE,
      '#default_value' => $config->get('proxy_auth_key'),
    ];

    $form['disable_default_styles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Disable the default feature's theme"),
      '#required' => FALSE,
      '#description' => $this->t('If you do not want default styling, you can disable it.'),
      '#default_value' => $config->get('disable_default_styles'),
    ];
    $form['manage_commands_groups'] = [
      '#type' => 'details',
      '#title' => $this->t('Manage commands groups'),
      '#open' => FALSE,
      '#description' => $this->t('You can add extra AI Commands to AI Assistant'),
    ];
    $form['manage_commands_groups']['go_to_manage'] = [
      '#type' => 'submit',
      '#value' => $this->t('Commands group list'),
      '#submit' => ['::manageCommands'],
    ];

    return $form;
  }

  /**
   * Redirect to AI Command group collection.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function manageCommands(array $form, FormStateInterface $form_state):void {
    $form_state->setRedirect('entity.ckeditor5_ai_command_group.collection');
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config($this->getFormId())
      ->setData($form_state->cleanValues()->getValues())
      ->save();
    parent::submitForm($form, $form_state);
  }

}
