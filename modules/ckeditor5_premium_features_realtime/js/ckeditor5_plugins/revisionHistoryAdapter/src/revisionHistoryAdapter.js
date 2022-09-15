

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
    const addRevisionOnSubmit = true;

    revisionHistoryConfig.viewerContainer = document.querySelector('.revision-history-container-data'),
    revisionHistoryConfig.viewerEditorElement = document.querySelector('.revision-viewer-editor');
    revisionHistoryConfig.viewerSidebarContainer = document.querySelector('.revision-viewer-sidebar');
    revisionHistoryConfig.editorContainer = document.querySelector('.ck-editor-sidebar-wrapper');
    // Initialize plugin.
    const revisionTrackerPlugin = this.editor.plugins.get('RevisionTracker');

    // Hook to form submit.
    const form = this.editor.sourceElement.closest('form');
    form.addEventListener("submit", (e) => {
      this.updateStorage(revisionHistoryPlugin, revisionTrackerPlugin, addRevisionOnSubmit)
    });
  }

  async updateStorage(plugin, tracker, addRevisionOnSubmit) {
    await tracker.update();
    if (addRevisionOnSubmit) {
      await tracker.saveRevision({name: 'Entity save'});
    }
  }
}

export default RevisionHistoryAdapter;
