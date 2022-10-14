(function ($, Drupal) {
  Drupal.CKEditor5PremiumFeatures = {

    editorContentExportProcessor: async function(editor) {
      this.editor = editor;

      let editorContent = this.getEditorContent();

      editorContent = await Drupal.CKEditor5PremiumFeatures.mediaTagsConverter.convertMediaTags(
        editorContent,
        editor.sourceElement.dataset.editorActiveTextFormat
      );
      editorContent = Drupal.CKEditor5PremiumFeatures.relativePathsProcessor(editorContent);

      return editorContent;
    },

    getEditorContent() {
      return this.editor.getData( {
        showSuggestionHighlights: true,
      });
    },
  }
})(jQuery, Drupal);
