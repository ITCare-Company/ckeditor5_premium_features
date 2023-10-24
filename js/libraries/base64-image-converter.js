/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

(function ($, Drupal) {
  Drupal.CKEditor5PremiumFeatures.base64ImageConverter = {

    /**
     * Convert images url into base64 in HTML
     * @param content
     *   Document content.
     *
     * @returns {Promise<string>}
     */
    async convert(content) {
      return new Promise( async resolve => {
        let result = await new Promise( resolve => {
          $.post('/ck5/api/base64-image-converter', {
            document: JSON.stringify(content),
          }).done(function(result) {
            resolve(result);
          });
        });
        resolve(result.document);
      });
    },
  }
})(jQuery, Drupal);
