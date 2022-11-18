<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5_premium_features\Config\ExportFeaturesConfigHandlerInterface;
use Drupal\ckeditor5_premium_features\Form\SharedBuildConfigFormInterface;
use Drupal\ckeditor5_premium_features\Generator\FileNameGeneratorInterface;
use Drupal\ckeditor5_premium_features\Utility\CssStyleProvider;
use Drupal\ckeditor5_premium_features\Utility\FormElement;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\editor\EditorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CKEditor 5 export related modules base plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class ExportBase extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface, ContainerFactoryPluginInterface {
  use CKEditor5PluginConfigurableTrait;

  /**
   * The settings form object.
   *
   * @var \Drupal\ckeditor5_premium_features\Form\SharedBuildConfigFormInterface
   */
  protected SharedBuildConfigFormInterface $settingsForm;

  /**
   * Creates the plugin instance.
   *
   * @param string $featurePlugin
   *   The id of the faeture plugin.
   * @param string $settingsFormClass
   *   The settings form class namespace.
   *   The generator filename service.
   * @param string $fileExtension
   *   File extension used in exported file.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\ckeditor5_premium_features\Config\ExportFeaturesConfigHandlerInterface $settingsConfigHandler
   *   The settings configuration handler.
   * @param \Drupal\ckeditor5_premium_features\Generator\FileNameGeneratorInterface $fileNameGenerator
   *   The file name generator service.
   * @param \Drupal\ckeditor5_premium_features\Utility\CssStyleProvider $cssStyleProvider
   *   The style css list provider service.
   * @param mixed ...$parent_arguments
   *   The parent plugin arguments.
   *
   * @throws \ReflectionException
   */
  public function __construct(
    protected string $featurePlugin,
    protected string $settingsFormClass,
    protected string $fileExtension,
    protected ConfigFactoryInterface $configFactory,
    protected ExportFeaturesConfigHandlerInterface $settingsConfigHandler,
    protected FileNameGeneratorInterface $fileNameGenerator,
    protected CssStyleProvider $cssStyleProvider,
    ...$parent_arguments
  ) {
    parent::__construct(...$parent_arguments);
    $this->settingsForm = (new \ReflectionClass($this->settingsFormClass))->newInstanceWithoutConstructor();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $config = $plugin_definition->toArray()['drupal']['premium_features'];

    return new static(
      $config['plugin'],
      $config['settings_form'],
      $config['file_extension'],
      $container->get('config.factory'),
      $container->get('ckeditor5_premium_features.config_handler.export_settings')->setConfig($config['configuration']),
      $container->get('ckeditor5_premium_features.file_name_generator'),
      $container->get('ckeditor5_premium_features.css_style_provider'),
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * Gets the feature plugin.
   *
   * @return string
   *   The CKEditor plugin name.
   */
  public function getFeaturePlugin(): string {
    return $this->featurePlugin;
  }

  /**
   * Get file extension.
   *
   * @return string
   *   Export file extension.
   */
  public function getFileExtension(): string {
    return $this->fileExtension;
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $plugin = $this->getFeaturePlugin();

    if ($this->settingsConfigHandler->hasConverterUrl()) {
      $static_plugin_config[$plugin]['converterUrl'] = $this->settingsConfigHandler->getConverterUrl();
    }

    $static_plugin_config[$plugin]['converterOptions'] = $this->getCurrentConfiguration();

    $file_extension = $this->getFileExtension();
    $file_name = $this->fileNameGenerator->generateFromRequest();
    $this->fileNameGenerator->addExtensionFile($file_name, $file_extension);
    $static_plugin_config[$plugin]['fileName'] = $file_name;
    $static_plugin_config[$plugin]['stylesheets'] = $this->cssStyleProvider->getFormattedListOfCssFiles();

    return $static_plugin_config;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->get($this->getPluginId());

    $global_options = $this->settingsConfigHandler->getConverterOptions();
    $override_global = $this->configuration['override_global'] ?? FALSE;

    $form['override_global'] = [
      '#type' => 'checkbox',
      '#title' => 'Override global settings',
      '#description' => $this->t('Using below form you can overwrite the <a href="@url">global export settings </a>.', [
        '@url' => Url::fromRoute($this->settingsForm::getSettingsRouteName())->toString(),
      ]),
      '#default_value' => $override_global,
    ];

    $config->initWithData($this->configuration);

    $export_form = $this->settingsForm::form($form, $form_state, $config);
    unset($export_form['converter_url']);

    FormElement::setPlaceholders($export_form, $global_options);

    if (!$override_global) {
      FormElement::disableFormFields($export_form);
    }

    return $export_form;
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
    $this->configuration = $form_state->cleanValues()->getValues();

    unset($this->configuration['converter_options']['header']['actions']);
    unset($this->configuration['converter_options']['footer']['actions']);
  }

  /**
   * Returns final plugin configuration.
   */
  protected function getCurrentConfiguration(): array {
    $global_config = array_filter($this->settingsConfigHandler->getConverterOptions());
    if (!$this->configuration['override_global']) {
      return $global_config;
    }

    $format_config = array_filter($this->configuration['converter_options']);

    /*
     * Here we are merging two configurations, from the custom settings form nad from the text format plugin page.
     * The current order, means that the plugin settings will overwrite the custom settings form values.
     */
    return NestedArray::mergeDeepArray([
        $global_config,
        $format_config,
      ], TRUE);

  }

}
