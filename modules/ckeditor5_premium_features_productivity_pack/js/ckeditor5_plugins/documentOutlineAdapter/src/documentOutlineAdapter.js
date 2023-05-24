
/* global document */

export default class DocumentOutlineAdapter {

  constructor( editor ) {
    this.editor = editor;
    this.elementId = this.editor.sourceElement.dataset.drupalSelector;

    const document_outline_id = this.elementId.replace('-value', '-document-outline-container');
    let document_outline_wrapper = document.getElementById(document_outline_id);

    console.log(document_outline_wrapper);

    if (typeof document_outline_wrapper === 'undefined' || !document_outline_wrapper) {
      return;
    }

    editor.config._config.documentOutline = {'container': document_outline_wrapper};
  }

  static get pluginName() {
    return 'DocumentOutlineAdapter';
  }

  init() {

  }

}
