(function ($, Drupal) {
  Drupal.CKEditor5PremiumFeatures = {

    editorContentExportProcessor: function(editor) {
      this.editor = editor;

      let editorContent = this.getEditorContent();

      editorContent = Drupal.CKEditor5PremiumFeatures.mediaTagsConverter.convertMediaTags(editorContent);
      editorContent = Drupal.CKEditor5PremiumFeatures.relativePathsProcessor(editorContent);

      console.log('after processing');
      console.log(editorContent);

      return editorContent;
    },

    getEditorContent() {
      return this.editor.getData( {
        showSuggestionHighlights: true,
      });
    },
  }
})(jQuery, Drupal);
