<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Utility;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Theme\ThemeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Css style list provider.
 */
class CssStyleProvider implements ContainerFactoryPluginInterface {

  /**
   * Creates CssStyleProvider instance.
   *
   * @param \Drupal\Core\Theme\ThemeManager $themeManager
   *   Theme ThemeManager service.
   */
  public function __construct(protected ThemeManager $themeManager) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('theme.manager'),
    );
  }

  /**
   * Check if uri ist font css.
   *
   * @param string $url
   *   Url to style file.
   *
   * @return bool
   *   Is font url.
   */
  private function isFontCssFile(string $url): bool {
    return str_ends_with($url, 'fonts.css') || str_starts_with($url, '//fonts');
  }

  /**
   * Get List of styles urls based on type.
   *
   * @param bool $fonts_list
   *   Type of list which will be rerun.
   *
   * @return array
   *   Css file style list.
   */
  public function getFontsUrlsList(bool $fonts_list = FALSE): array {
    $styles_urls = $this->getCssFilesListFromActiveTheme();
    $fonts = [];
    $non_fonts = [];
    foreach ($styles_urls as $styles_url) {
      $is_font = $this->isFontCssFile($styles_url);
      if ($is_font) {
        $fonts[] = $styles_url;
      }
      else {
        $non_fonts[] = $styles_url;
      }
    }

    return $fonts_list ? $fonts : $non_fonts;
  }

  /**
   * Get list of css styles file used in current theme.
   *
   * @return array
   *   List of urls.
   */
  public function getCssFilesListFromActiveTheme(): array {
    $active_theme = $this->themeManager->getActiveTheme();

    return _ckeditor5_theme_css($active_theme->getName());
  }

  /**
   * Get list all css used in current editor instance.
   *
   * Formatted in pattern:
   *
   * @see isFontCssFile();
   * - Fonts files.
   * - EDITOR_STYLES (default one).
   * - All others (non fonts).
   */
  public function getFormattedListOfCssFiles(): array {
    $fonts = $this->getFontsUrlsList(TRUE);
    $non_fonts = $this->getFontsUrlsList();

    return array_merge($fonts, ['EDITOR_STYLES'], $non_fonts);
  }

}
