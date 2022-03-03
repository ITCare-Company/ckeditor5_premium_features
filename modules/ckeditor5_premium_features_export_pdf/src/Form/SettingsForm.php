<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_export_pdf\Form;

use Drupal\ckeditor5_premium_features_export_pdf\Enum\Config;
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
      '#title' => $this->t('Export to PDF'),
      '#open' => TRUE,
      '#description' =>
      $this->t("Premium features will work only if configured correctly. If you haven't subscribed yet, you cen start <a href='@trial'>a free trial</a>.", ['@trial' => 'https://orders.ckeditor.com/trial/premium-features'])
      . '<br>'
      // @todo define the documentation URL.
      . $this->t("Follow the <a href='@documentation'>dedicated documentation for Drupal</a> as most of the steps necessary to run premium features have been already included in this module.", ['@documentation' => '#']),
    ];

    $configuration = [];

    $this->setDefaultValues($configuration);

    $form['configuration'] = $configuration + $form['configuration'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {

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
