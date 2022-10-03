import CollaborationStorage
  from "../../../../../../js/ckeditor5_plugins/collaborationStorage/src/collaborationStorage";


class RealtimeRevisionHistoryAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
  }

  static get pluginName() {
    return 'RealtimeRevisionHistoryAdapter'
  }

  static get requires() {
    return ['RevisionTracker']
  }

  afterInit() {
    this.elementId = this.editor.sourceElement.dataset.ckeditor5PremiumElementId;
    const revisionHistoryConfig = this.editor.config._config.revisionHistory;

    revisionHistoryConfig.viewerContainer = document.querySelector(`.revision-history-container-data[data-ckeditor5-premium-element-id="${this.elementId}"]`);
    revisionHistoryConfig.viewerEditorElement = revisionHistoryConfig.viewerContainer.querySelector('.revision-viewer-editor');
    revisionHistoryConfig.viewerSidebarContainer = revisionHistoryConfig.viewerContainer.querySelector('.revision-viewer-sidebar');
    revisionHistoryConfig.editorContainer = revisionHistoryConfig.viewerContainer.parentElement.querySelector('.ck-editor-sidebar-wrapper');

    // Initialize plugin.
    const revisionHistoryPlugin = this.editor.plugins.get('RevisionHistory');
    const revisionTrackerPlugin = this.editor.plugins.get('RevisionTracker');

    // Hook to form submit.
    const form = this.editor.sourceElement.closest('form');
    form.addEventListener("submit", (e) => {
      this.updateStorage(revisionHistoryPlugin, revisionTrackerPlugin)
    });

    this.storage.processRevisionDisable();
  }

  async updateStorage(plugin, tracker) {
    await tracker.update();
    await tracker.saveRevision({name: 'Entity save'});
  }
}

export default RealtimeRevisionHistoryAdapter;
