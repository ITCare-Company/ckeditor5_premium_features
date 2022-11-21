<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the base class for the forms having the shared form structure.
 */
abstract class SharedBuildConfigFormBase extends ConfigFormBase implements SharedBuildConfigFormInterface {

  /**
   * {@inheritdoc}
   */
  abstract public function getFormId(): string;

  /**
   * {@inheritdoc}
   */
  abstract public static function getSettingsRouteName(): string;

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
  public static function form(array $form, FormStateInterface $form_state, Config $config): array {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config($this->getFormId());

    return static::form($form, $form_state, $config);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $cv = $form_state->cleanValues()->getValues();
    $this
      ->config($this->getFormId())
      ->setData($cv)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
