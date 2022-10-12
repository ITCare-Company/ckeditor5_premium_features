
class ExportPdfAdapter {
  constructor( editor ) {
    editor.config._config.exportPdf.dataCallback = (editor) => {
      return Drupal.CKEditor5PremiumFeatures.editorContentExportProcessor(editor);
    }
  }

  static get pluginName() {
    return 'ExportPdfAdapter'
  }
}

export default ExportPdfAdapter;
