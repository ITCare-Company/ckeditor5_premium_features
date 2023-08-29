<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_import_word\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure CKEditor 5 Import from Word settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckeditor5_premium_features_import_word_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['ckeditor5_premium_features_import_word.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['word_styles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Word's default styles"),
      '#description' => $this->t('If checked, Word’s default styles will be preserved in the imported content. You can learn more about that feature in the <a target="_blank" href="@guides-url">Styles</a> guide for Import from Word.', ['@guides-url' => 'https://ckeditor.com/docs/cs/latest/guides/import-from-word/styles.html#default-styles']),
      '#default_value' => $this->config('ckeditor5_premium_features_import_word.settings')->get('word_styles'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('ckeditor5_premium_features_import_word.settings')
      ->set('word_styles', $form_state->getValue('word_styles'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
