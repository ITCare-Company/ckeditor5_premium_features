
import CollaborationStorage
  from "../../../../../../js/ckeditor5_plugins/collaborationStorage/src/collaborationStorage";

class ExportPdfAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
  }

  static get pluginName() {
    return 'ExportPdfAdapter'
  }

  afterInit() {
    this.editor.config._config.exportPdf.dataCallback = editor => editor.getData( {
      showSuggestionHighlights: false,
      skipNotAttached: true,
    } );
  }
}

export default ExportPdfAdapter;
