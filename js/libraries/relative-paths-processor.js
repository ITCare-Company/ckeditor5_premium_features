(function ($, Drupal) {
  Drupal.CKEditor5PremiumFeatures.relativePathsProcessor = function(content) {
    return '<base href="' + window.location.origin + '">'
      + content
  }
}) (jQuery, Drupal);
