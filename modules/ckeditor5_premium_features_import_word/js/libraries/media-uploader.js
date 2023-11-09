/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

(function ($, Drupal) {
  Drupal.CKEditor5PremiumFeaturesImportWord = {

    /**
     * Upload base64 image and create media entity.
     *
     * @param imageContent
     *   Image content.
     * @param format
     *   Text format.
     *
     * @returns {Promise<string>}
     */
    async uploadMediaFromBase64(imageContent, format) {
      return new Promise( async resolve => {
        $.post('/ckeditor5-premium-features/import-word/upload-media/' + format, {
          image: imageContent,
        }).done(function(result) {
          resolve(result.mediaUuid);
        });
      });
    },

  }
})(jQuery, Drupal);
