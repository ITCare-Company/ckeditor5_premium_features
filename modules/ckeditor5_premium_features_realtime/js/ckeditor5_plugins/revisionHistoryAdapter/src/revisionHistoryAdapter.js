

class RevisionHistoryAdapter {
  constructor( editor ) {
    this.editor = editor;
  }

  static get pluginName() {
    return 'RevisionHistoryAdapter'
  }

  static get requires() {
    return ['RevisionTracker']
  }

  init() {
    const revisionHistoryConfig = this.editor.config._config.revisionHistory;

    revisionHistoryConfig.viewerContainer = document.querySelector('.revision-history-container-data'),
    revisionHistoryConfig.viewerEditorElement = document.querySelector('.revision-viewer-editor');
    revisionHistoryConfig.viewerSidebarContainer = document.querySelector('.revision-viewer-sidebar');
    revisionHistoryConfig.editorContainer = document.querySelector('.ck-editor-sidebar-wrapper');
    // Initialize plugin.
    const revisionTrackerPlugin = this.editor.plugins.get('RevisionTracker');

    // Hook to form submit.
    const form = this.editor.sourceElement.closest('form');
    form.addEventListener("submit", (e) => {
      this.updateStorage(revisionHistoryPlugin, revisionTrackerPlugin)
    });
  }

  /**
   * Executed after plugin is initialized.
   *
   * For the RTC it's the most suitable place to dynamically disable toolbar items.
   */
  afterInit() {
    this.processRevisionDisable();
  }

  /**
   * Checks if collaboration is set to be disabled and blocks the revision history feature (button).
   *
   * @returns {boolean}
   *   TRUE if feature was blocked, FALSE otherwise.
   *
   * @todo: CCP-201 - refactor
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
   *
   * @todo: CCP-201 - refactor
   */
  isCollaborationDisabled() {
    return typeof drupalSettings.ckeditor5Premium != 'undefined' &&
      typeof drupalSettings.ckeditor5Premium.disableCollaboration != "undefined" &&
      drupalSettings.ckeditor5Premium.disableCollaboration === true;
  }

  async updateStorage(plugin, tracker) {
    await tracker.update();
    await tracker.saveRevision({name: 'Entity save'});
  }
}

export default RevisionHistoryAdapter;
