<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_premium_features\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\Order;

/**
 * Hook implementations for ckeditor5_premium_features.
 */
class Ckeditor5PremiumFeaturesLibraryHooks {

  /**
   * Implements hook_library_info_alter().
   *
   * D12 (change record 3496788): moved from a bare procedural function to
   * this OOP method so the order: Order::Last parameter actually takes
   * effect. Core's HookCollectorPass only reads a #[Hook] attribute's order
   * parameter when it is on a real class method (OOP scan branch); the
   * procedural-file scan branch never does (see
   * HookCollectorPass::collectModuleHookImplementations(),
   * core/lib/Drupal/Core/Hook/HookCollectorPass.php:397-405 vs. 424-466).
   * The old ckeditor5_premium_features_module_implements_alter() procedural
   * reorder is left in place as dead code for pre-11.2 BC.
   */
  #[Hook('library_info_alter', order: Order::Last)]
  public function libraryInfoAlter(&$libraries, $extension) {
    // @todo Change this to use `after` instead of `dependencies` after https://www.drupal.org/project/drupal/issues/1945262 is released.
    $validForm = isset($libraries['node-form']) ? 'node-form' : (isset($libraries['form-two-columns']) ? 'form-two-columns' : NULL);
    if ($extension === 'claro' && $validForm) {
      $cke5PremiumFeaturesSettings = \Drupal::configFactory()->get('ckeditor5_premium_features.settings');
      if ($cke5PremiumFeaturesSettings->get('alter_node_form_css')) {
        $libraries[$validForm]['dependencies'][] = 'ckeditor5_premium_features/claro--override--node-form';
      }
    }

    if ($extension === 'core' && isset($libraries['ckeditor5'])) {
      $libraries['ckeditor5']['dependencies'][] = 'ckeditor5_premium_features/distribution-channel';
    }

    $isCoreWithoutAutoformat = version_compare(\Drupal::VERSION, '10.1.0', '<');
    if ($isCoreWithoutAutoformat && $extension === 'ckeditor5_premium_features' && isset($libraries['collaboration-integration-base'])) {
      $key = array_search('core/ckeditor5.autoformat', $libraries["collaboration-integration-base"]["dependencies"]);
      if ($key !== FALSE) {
        unset($libraries["collaboration-integration-base"]["dependencies"][$key]);
      }
      $libraries["collaboration-integration-base"]["dependencies"][] = 'ckeditor5_premium_features/autoformat';
    }

    if ($extension === 'ckeditor5_premium_features_wproofreader') {
      $libraryDiscovery = \Drupal::service('library.discovery');
      $ckeditorLibrary = $libraryDiscovery->getLibraryByName('core', 'ckeditor5');

      if (version_compare($ckeditorLibrary['version'], '41.0.0', '<')) {
        $libraries["wproofreader"]["js"] = [
          'js/build/wproofreader_3_27.js' => [
            'minified' => TRUE,
          ],
        ];
      }
    }
  }

}
