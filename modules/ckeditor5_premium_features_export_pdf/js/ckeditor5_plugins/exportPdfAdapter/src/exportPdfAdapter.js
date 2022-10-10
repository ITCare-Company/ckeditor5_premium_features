
class ExportPdfAdapter {
  constructor( editor ) {
    console.log(editor.config._config.exportPdf);

    editor.config._config.exportPdf.dataCallback = (editor) => {
      let editorContent = Drupal.CKEditor5PremiumFeatures.editorContentExportProcessor(editor);

      return editorContent
    }
  }

  static get pluginName() {
    return 'ExportPdfAdapter'
  }
}

export default ExportPdfAdapter;
