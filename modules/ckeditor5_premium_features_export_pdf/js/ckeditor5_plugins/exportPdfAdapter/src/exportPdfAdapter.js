
class ExportPdfAdapter {
  constructor( editor ) {
    editor.config._config.exportPdf.dataCallback = editor => editor.getData( {
        showSuggestionHighlights: true,
    });
  }

  static get pluginName() {
    return 'ExportPdfAdapter'
  }
}

export default ExportPdfAdapter;
