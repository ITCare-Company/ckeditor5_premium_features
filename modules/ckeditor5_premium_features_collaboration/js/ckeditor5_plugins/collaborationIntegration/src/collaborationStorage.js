class CollaborationStorage {
  constructor( editor ) {
    this.editor = editor;
    this.elementId = this.editor.sourceElement.dataset.ckeditor5PremiumElementId;
  }

  getSourceDataSelector(type) {
    const types = {
      'trackChanges': '.track-changes',
      'comments': '.comments',
    };

    const cssClass = types[type] + '-data';
    const dataAttribute = `[data-ckeditor5-premium-element-id="${this.elementId}"]`;

    return cssClass + dataAttribute;
  }
}

export default CollaborationStorage;
