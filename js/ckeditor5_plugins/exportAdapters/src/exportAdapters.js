class ExportAdapters {
  constructor( editor ) {
    // Attach custom dataCallback when PDF export is enabled.
    if (editor.config._config.exportPdf && typeof editor.config._config.exportPdf !== 'undefined') {
      editor.config._config.exportPdf.dataCallback = (editor) => {
        return Drupal.CKEditor5PremiumFeatures.editorContentExportProcessor(editor, TRUE);
      }
    }

    // Attach custom dataCallback when Word export is enabled.
    if (editor.config._config.exportWord && typeof editor.config._config.exportWord !== 'undefined') {
      editor.config._config.exportWord.dataCallback = (editor) => {
        return Drupal.CKEditor5PremiumFeatures.editorContentExportProcessor(editor, FALSE);
      }
    }
  }

  static get pluginName() {
    return 'ExportAdapters'
  }
}

export default ExportAdapters;
