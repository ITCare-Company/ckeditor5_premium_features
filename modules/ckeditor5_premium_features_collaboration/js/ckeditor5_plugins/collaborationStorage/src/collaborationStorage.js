class CollaborationStorage {
  constructor( editor ) {
    this.editor = editor;
    this.elementId = this.editor.sourceElement.dataset.ckeditor5PremiumElementId;
  }

  processCollaborationCommandDisabled(commandName) {
    if (!this.isCollaborationDisabled()) {
      return false;
    }

    const command = this.editor.commands._commands.get( commandName );

    if (typeof command == 'undefined') {
      return true;
    }

    command.forceDisabled( 'premium-features-module' );

    return true;
  }

  isRevisionDisabled() {
    if (!this.isCollaborationDisabled()) {
      return false;
    }

    if (this.editor.plugins.has( 'RevisionTracker' )) {

      this.editor.plugins.get( 'RevisionTracker' ).isEnabled = false;
    }

    return true;
  }

  isCollaborationDisabled() {
    return typeof drupalSettings.ckeditor5Premium != 'undefined' &&
      typeof drupalSettings.ckeditor5Premium.disableCollaboration != "undefined" &&
      drupalSettings.ckeditor5Premium.disableCollaboration === true;
  }

  getSourceDataSelector(type) {
    const types = {
      'trackChanges': '.track-changes',
      'comments': '.comments',
      'revisionHistory': '.revision-history',
      'revisionHistoryContainer': '.revision-history-container',
    };

    const cssClass = types[type] + '-data';
    const dataAttribute = `[data-ckeditor5-premium-element-id="${this.elementId}"]`;

    return cssClass + dataAttribute;
  }
}

export default CollaborationStorage;
