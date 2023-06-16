<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features_productivity_pack\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 Productivity Pack Base Plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ProductivityPackBase extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, ContainerFactoryPluginInterface {

  use CKEditor5PluginConfigurableTrait;

  const PLUGIN_CONFIG_NAME = 'ckeditor5_premium_features_productivity_pack_base';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      DocumentOutline::CONFIG_FIELD_ENABLED => FALSE,
      SlashCommand::CONFIG_FIELD_ENABLED => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form[DocumentOutline::CONFIG_FIELD_ENABLED] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Document Outline'),
      '#default_value' => $this->configuration[DocumentOutline::CONFIG_FIELD_ENABLED] ?? FALSE,
      '#description' => t('Enable Document Outline in the editor'),
    ];
    $form[SlashCommand::CONFIG_FIELD_ENABLED] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Slash Command'),
      '#default_value' => $this->configuration[SlashCommand::CONFIG_FIELD_ENABLED] ?? FALSE,
      '#description' => t('Enable Slash Command in the editor'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $formValues = $form_state->getValues();
    $isDocumentOutlineEnabled = $formValues[DocumentOutline::CONFIG_FIELD_ENABLED] ?? FALSE;
    $isSlashCommandEnabled = $formValues[SlashCommand::CONFIG_FIELD_ENABLED] ?? FALSE;
    $this->configuration[DocumentOutline::CONFIG_FIELD_ENABLED] = (bool) $isDocumentOutlineEnabled;
    $this->configuration[SlashCommand::CONFIG_FIELD_ENABLED] = (bool) $isSlashCommandEnabled;
  }

}
