<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Form;

use Drupal\ckeditor5_premium_features\Enum\Config;
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

    $configuration['env'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Environment ID'),
      '#description' =>
      $this->t('The environment management panel can be found in <a href="@dashboard">CKEditor dashboard</a>.', ['@dashboard' => $dashboard_url])
      . '<br>'
      . $this->t('Required for Export to Word/PDF and Real-time collaboration.'),
    ];

    $configuration['access_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Access key'),
      '#description' =>
      $this->t('The access key to the environment can be found in the <a href="@dashboard">CKEditor dashboard</a>.', ['@dashboard' => $dashboard_url])
      . '<br>'
      . $this->t('Required for Export to Word/PDF and Real-time collaboration.'),
    ];

    $configuration['dev_token_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Development token URL'),
      '#description' => $this->t('The development token URL should be used with care as it does not provide sufficient permission validation. It is highly recommended to specify Environment ID and Access Key instead.'),
      '#attributes' => [
        'placeholder' => 'https://',
      ],
    ];

    $this->setDefaultValues($configuration);

    $form['configuration'] = $configuration + $form['configuration'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $dev_token_url = $form_state->getValue('dev_token_url', FALSE);
    $access_key = $form_state->getValue('access_key', FALSE);
    $env = $form_state->getValue('env', FALSE);

    if ($dev_token_url && $access_key && $env) {
      $form_state->setErrorByName(
        'dev_token_url',
        $this->t('A combination of Environment ID/Access Key and Development token URL cannot be used together. Specify either the Environment ID/Access Key or the Development Token URL')
      );
    }


    parent::validateForm($form, $form_state);
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
      $elements[$key]['#default_value'] = $config->get($key);
    }
  }

}
