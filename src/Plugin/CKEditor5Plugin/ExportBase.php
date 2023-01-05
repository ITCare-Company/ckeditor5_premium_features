<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5_premium_features\Config\ExportFeaturesConfigHandlerInterface;
use Drupal\ckeditor5_premium_features\Form\BaseExportSettingsForm;
use Drupal\ckeditor5_premium_features\Form\SharedBuildConfigFormInterface;
use Drupal\ckeditor5_premium_features\Generator\FileNameGeneratorInterface;
use Drupal\ckeditor5_premium_features\Utility\CssStyleProvider;
use Drupal\ckeditor5_premium_features\Utility\FormElement;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
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

  const CUSTOM_CSS_DIRECTORY_PATH = 'public://styles/ckeditor5/export/';

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
    protected FileSystemInterface $fileSystem,
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
      $container->get('file_system'),
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
    $customCssFile = $this->getCustomCssFilePath($editor->getOriginalId());
    if ($customCssFile) {
      $static_plugin_config[$plugin]['stylesheets'][] = $customCssFile;
    }
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
    $global_custom_css = $this->configuration['custom_css'] ?? NULL;
    $override_global = $this->configuration['override_global'] ?? FALSE;

    $form['override_global'] = [
      '#type' => 'checkbox',
      '#title' => 'Override global settings',
      '#description' => $this->t('Using below form you can overwrite the <a href="@url">global export settings </a>.', [
        '@url' => Url::fromRoute($this->settingsForm::getSettingsRouteName())->toString(),
      ]),
      '#default_value' => $override_global,
    ];

    if (!$override_global) {
      $config->initWithData([
        'custom_css' => $global_custom_css,
        'converter_options' => $global_options,
      ]);
    }
    else {
      $config->initWithData($this->configuration);
    }

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

    if (!empty($this->configuration['override_global']) &&
        $this->settingsForm instanceof BaseExportSettingsForm) {
      $formObject = $form_state->getFormObject();
      $editor = $formObject->getEntity();
      $fileName = $this->settingsForm->getCustomCssFileName() . '-' . $editor->getOriginalId();
      $this->cssStyleProvider->updateCustomCssFile($this->configuration['custom_css'], $fileName);
    }

    unset($this->configuration['converter_options']['header']['actions']);
    unset($this->configuration['converter_options']['footer']['actions']);
  }

  /**
   * Returns final plugin configuration.
   */
  protected function getCurrentConfiguration(): array {
    $global_config = array_filter($this->settingsConfigHandler->getConverterOptions());
    $this->processConfigCleanup($global_config);

    if (empty($this->configuration['override_global']) && !empty($global_config)) {
      return $global_config;
    }

    $format_config = array_filter($this->configuration['converter_options']);
    $this->processConfigCleanup($format_config);

    /*
     * Here we are merging two configurations, from the custom settings form
     * and from the text format plugin page. The current order, means that
     * the plugin settings will overwrite the custom settings form values.
     */
    $merged_config = NestedArray::mergeDeepArray([
      $global_config,
      $format_config,
    ], TRUE);

    $adds = [
      'header',
      'footer',
    ];
    foreach ($adds as $placement) {
      if (isset($format_config[$placement])) {
        $merged_config[$placement] = $format_config[$placement];
      }
    }

    return $merged_config;
  }

  /**
   * Processing export configuration to perform required clean-ups.
   *
   * @param array $config
   *   Config array to be processed.
   */
  protected function processConfigCleanup(array &$config): void {
    $margins = [
      'top',
      'bottom',
      'left',
      'right',
    ];
    foreach ($margins as $direction) {
      $key = 'margin_' . $direction;
      if (empty($config[$key]) || !is_array($config[$key])) {
        continue;
      }
      $config[$key] = $config[$key]['value'] . $config[$key]['units'];
    }

    $adds = [
      'header',
      'footer',
    ];
    foreach ($adds as $placement) {
      unset($config[$placement]['actions']);
      if (!isset($config[$placement])) {
        continue;
      }
      if (is_array($config[$placement])) {
        foreach ($config[$placement] as $key => $item) {
          if ($item['html'] == '') {
            unset($config[$placement][$key]);
          }
        }
      }
      if (empty($config[$placement])) {
        unset($config[$placement]);
      }
    }

    $config = array_filter($config);
  }

  /**
   * Get the file with custom css.
   *
   * @param string $editorId
   *   The Editor id.
   *
   * @return string|bool
   *   The file path for custom css or null.
   */
  protected function getCustomCssFilePath(string $editorId): bool|string {
    if (!$this->settingsForm instanceof BaseExportSettingsForm) {
      return FALSE;
    }

    if (!empty($this->configuration['override_global'])) {
      $fileName = $this->settingsForm->getCustomCssFileName() . '-' . $editorId;
    }
    else {
      $fileName = $this->settingsForm->getCustomCssFileName();
    }

    return $this->cssStyleProvider->getCustomCssFile($fileName);
  }

}
