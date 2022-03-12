<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Form;

use Drupal\ckeditor5_premium_features\Enum\Config;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the form for the main module & submodule configuration.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return Config::SETTINGS->name();
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

    $form['configuration'] = [
      '#type' => 'details',
      '#title' => $this->t('Premium features configuration'),
      '#open' => TRUE,
      '#description' =>
      $this->t("Premium features will work only if configured correctly. If you haven't subscribed yet, you cen start <a href='@trial'>a free trial</a>.", ['@trial' => 'https://orders.ckeditor.com/trial/premium-features'])
      . '<br>'
      // @todo define the documentation URL.
      . $this->t("Follow the <a href='@documentation'>dedicated documentation for Drupal</a> as most of the steps necessary to run premium features have been already included in this module.", ['@documentation' => '#']),
    ];

    $configuration = [];

    $dashboard_url = 'https://dashboard.ckeditor.com/';

    $configuration['license_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('License key'),
      '#description' => $this->t('The license key is required <strong>only</strong> for Track changes and Comments (<strong>without</strong> real-time collaboration).'),
    ];

    $configuration['auth_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Authroization type'),
      '#options' => [
        'key' => $this->t('Access key'),
        'dev_token' => $this->t('Development token'),
      ],
      '#default_value' => 'key',
      '#description' => $this->t('Select the authorization type for your features. The access key-based authorization is highly recommended and the best option in most cases.'),
    ];

    $configuration['env'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Environment ID'),
      '#description' =>
      $this->t('The environment management panel can be found in <a href="@dashboard">CKEditor dashboard</a>.', ['@dashboard' => $dashboard_url])
      . '<br>'
      . $this->t('Required for Export to Word/PDF and Real-time collaboration.'),
      '#states' => [
        'visible' => [
          'select[name="auth_type"]' => ['value' => 'key'],
        ],
      ],
    ];

    $configuration['access_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access key'),
      '#description' =>
      $this->t('The access key to the environment can be found in the <a href="@dashboard">CKEditor dashboard</a>.', ['@dashboard' => $dashboard_url])
      . '<br>'
      . $this->t('Required for Export to Word/PDF and Real-time collaboration.'),
      '#states' => [
        'visible' => [
          'select[name="auth_type"]' => ['value' => 'key'],
        ],
      ],
    ];

    $configuration['dev_token_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Development token URL'),
      '#description' => $this->t('The development token URL should be used with care as it does not provide sufficient permission validation. It is highly recommended to specify Environment ID and Access Key instead.'),
      '#attributes' => [
        'placeholder' => 'https://',
      ],
      '#states' => [
        'visible' => [
          'select[name="auth_type"]' => ['value' => 'dev_token'],
        ],
      ],
    ];

    $configuration['dev_token_accept'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I understand the consequences of using a development token URL'),
      '#states' => [
        'required' => [
          'select[name="auth_type"]' => ['value' => 'dev_token'],
        ],
        'visible' => [
          'select[name="auth_type"]' => ['value' => 'dev_token'],
        ],
      ],
    ];

    $this->setDefaultValues($configuration);

    $form['configuration'] = $configuration + $form['configuration'];

    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced settings'),
      '#open' => TRUE,
      '#description' =>
      $this->t('CKEditor Premium Features needs to load additional plugins (“DLLs”) in order to run. By default this module will detect the version of CKEditor your website is running and load automatically required plugins from a CDN.')
      . '<br>'
      . $this->t('Specify the DLL packages location only if you host the DLL packages by yourself. Contact us in case of any questions.'),
    ];

    $advanced['dll_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('DLL packages location'),
      '#description' => $this->t('Leave this field empty unless you know what you are doing.'),
    ];

    $this->setDefaultValues($advanced);

    $form['advanced'] = $advanced + $form['advanced'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $dev_token_url = $form_state->getValue('dev_token_url', FALSE);
    $access_key = $form_state->getValue('access_key', FALSE);
    $env = $form_state->getValue('env', FALSE);
    $auth_type = $form_state->getValue('auth_type');

    $is_valid = TRUE;

    if ($auth_type === 'key' && $dev_token_url) {
      $is_valid = FALSE;
    }
    elseif ($auth_type === 'dev_token' && ($access_key || $env)) {
      $is_valid = FALSE;
    }

    if (!$is_valid) {
      $form_state->setErrorByName(
        'auth_type',
        $this->t('A combination of Environment ID/Access Key and Development token URL cannot be used together. Specify either the Environment ID/Access Key or the Development Token URL')
      );
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config($this->getFormId());
    $dll_changed = $config->get('dll_location') !== $form_state->getValue('dll_location');

    $config
      ->setData($form_state->cleanValues()->getValues())
      ->save();

    if ($dll_changed) {
      Cache::invalidateTags(['library_info']);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * Sets the default value on the form elements.
   *
   * It is taking the config value if present.
   *
   * @param array $elements
   *   The form elements to be processed.
   */
  private function setDefaultValues(array &$elements): void {
    $config = $this->config(($this->getFormId()));
    foreach ($elements as $key => $element) {
      $elements[$key]['#default_value'] = $config->get($key) ?? $element[$key]['#default_value'] ?? NULL;
    }
  }

}
