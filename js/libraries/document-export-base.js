/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

(function ($, Drupal) {
  Drupal.CKEditor5PremiumFeatures = {

    editorContentExportProcessor: async function(editor, enableHighlighting = true) {
      this.editor = editor;

      let editorContent = this.getEditorContent(enableHighlighting);

      editorContent = await Drupal.CKEditor5PremiumFeatures.mediaTagsConverter.convertMediaTags(
        editorContent,
        editor.sourceElement.dataset.editorActiveTextFormat
      );
      editorContent = Drupal.CKEditor5PremiumFeatures.relativePathsProcessor(editorContent);

      return editorContent;
    },

    getEditorContent(enableHighlighting = true) {
      return this.editor.getData( {
        showSuggestionHighlights: enableHighlighting,
      });
    },
  }
})(jQuery, Drupal);
