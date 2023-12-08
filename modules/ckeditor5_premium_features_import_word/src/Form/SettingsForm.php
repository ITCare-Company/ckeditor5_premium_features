<?php

/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_import_word\Form;

use Drupal\ckeditor5_premium_features\Utility\LibraryVersionChecker;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure CKEditor 5 Import from Word settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              TypedConfigManagerInterface $typedConfigManager,
                              protected LibraryVersionChecker $libraryVersionChecker) {
    parent::__construct($config_factory);
    $this->typedConfigManager = $typedConfigManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('ckeditor5_premium_features.core_library_version_checker')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ckeditor5_premium_features_import_word.settings';
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
    $config = $this->config($this->getFormId());
    $form['info'] = [
      '#markup' => $this->t('You can learn more about configuration options in the <a target="_blank" href="@guides-url">Styles</a> guide for Import from Word.', ['@guides-url' => 'https://ckeditor.com/docs/cs/latest/guides/import-from-word/styles.html#default-styles']),
    ];
    $form['word_styles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Word's default styles"),
      '#description' => $this->t('If checked, Word’s default styles will be preserved in the imported content.'),
      '#default_value' => $config->get('word_styles'),
    ];
    if ($this->libraryVersionChecker->isLibraryVersionHigherOrEqual('40.1.0')) {
      $form['css_resets'] = [
        '#type' => 'checkbox',
        '#title' => $this->t("CSS resets"),
        '#description' => $this->t('Setting this option will include all resetting styles in the HTML result, e.g. headings will have their font weight reset'),
        '#default_value' => $config->get('css_resets'),
      ];
      $form['comments_styles'] = [
        '#title' => $this->t("Comments styles"),
        '#type' => 'select',
        '#options' => [
          'basic' => $this->t('Only basic styles are kept.'),
          'none' => $this->t('Comment text is imported without any styling.'),
          'full' => $this->t('All styles are preserved (not recommended).'),
        ],
        '#description' => $this->t('If the imported document contains comments, only their basic styles will be kept by default. You can change it here.'),
        '#default_value' => $config->get('comments_styles') ?? 'basic',
      ];

      $form['disable_styles'] = [
        '#type' => 'checkbox',
        '#title' => $this->t("Disable styles"),
        '#description' => $this->t('Enabling this configuration will result in a document that does not include any Word styles.'),
        '#default_value' => $config->get('disable_styles'),
      ];
    }
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
    $cleanValues = $form_state->cleanValues()->getValues();
    $this->config($this->getFormId())
      ->setData($cleanValues)
      ->save();
    parent::submitForm($form, $form_state);
  }

}
