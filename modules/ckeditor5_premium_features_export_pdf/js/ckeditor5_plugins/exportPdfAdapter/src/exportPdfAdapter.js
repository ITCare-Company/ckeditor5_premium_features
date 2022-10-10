
class ExportPdfAdapter {
  constructor( editor ) {
    let baseHref = '<base href="' + window.location.origin + '">';

    editor.config._config.exportPdf.dataCallback = (editor) => {
      return baseHref + editor.getData( {
        showSuggestionHighlights: true,
      });
    }
  }

  static get pluginName() {
    return 'ExportPdfAdapter'
  }
}

export default ExportPdfAdapter;
