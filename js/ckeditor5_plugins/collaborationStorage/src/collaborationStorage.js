class CollaborationStorage {
  constructor( editor ) {
    this.editor = editor;
    this.elementId = this.editor.sourceElement.dataset.ckeditor5PremiumElementId;
  }

  /**
   * Checks if collaboration is set to be disabled and blocks the specified command (button).
   *
   * @param commandName
   *   Command name (related to a button)
   *
   * @returns {boolean}
   *   TRUE if command was blocked, FALSE otherwise.
   */
  processCollaborationCommandDisable(commandName) {
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

  /**
   * Checks if collaboration is set to be disabled and blocks the revision history feature (button).
   *
   * @returns {boolean}
   *   TRUE if feature was blocked, FALSE otherwise.
   */
  processRevisionDisable() {
    if (!this.isCollaborationDisabled()) {
      return false;
    }

    if (this.editor.plugins.has( 'RevisionTracker' )) {

      this.editor.plugins.get( 'RevisionTracker' ).isEnabled = false;
    }

    return true;
  }

  /**
   * Checks if collaboration is set to be disabled.
   *
   * @returns {boolean}
   *   TRUE if conditions for blocking collaboration are met, FALSE otherwise.
   */
  isCollaborationDisabled() {
    return typeof drupalSettings.ckeditor5Premium != 'undefined' &&
      typeof drupalSettings.ckeditor5Premium.disableCollaboration != "undefined" &&
      drupalSettings.ckeditor5Premium.disableCollaboration === true;
  }

  /**
   * Returns parent element of an editors' element matching passed ID.
   *
   * @param elementId
   *   HTML ID of an editor.
   *
   * @returns {HTMLElement|null}
   */
  getEditorParentContainer(elementId) {
    let editor_element = document.getElementById(elementId);

    while (editor_element && typeof editor_element !== "undefined"
      && typeof editor_element.classList !== "undefined" &&
      !editor_element.classList.contains('ck-editor-sidebar-wrapper')) {

      editor_element = editor_element.parentElement;
    }

    if (!editor_element || typeof editor_element === "undefined") {
      return null;
    }

    // We get parentElement one more time to be able to search for all related
    // editor elements (like sidebar, presence list etc)
    return editor_element.parentElement;
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
