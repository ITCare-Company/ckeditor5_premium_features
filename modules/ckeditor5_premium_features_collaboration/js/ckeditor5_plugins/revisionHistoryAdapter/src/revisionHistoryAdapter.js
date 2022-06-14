import CollaborationStorage from "../../collaborationStorage/src/collaborationStorage";

class RevisionHistoryAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
  }

  static get pluginName() {
    return 'RevisionHistoryAdapter'
  }

  static get requires() {
    return [ 'RevisionHistory' ]
  }

  init() {
    // Initialize revision history settings.
    const revisionHistoryConfig = this.editor.config._config.revisionHistory;
    let revisionHistoryContainer = document.querySelector(this.storage.getSourceDataSelector('revisionHistoryContainer'));
    if (revisionHistoryContainer === null) {
      revisionHistoryContainer = revisionHistoryConfig.viewerEditorElement.parentNode;
    }

    revisionHistoryConfig.viewerContainer = revisionHistoryContainer;
    revisionHistoryConfig.viewerEditorElement = revisionHistoryContainer.querySelector('.revision-viewer-editor');
    revisionHistoryConfig.viewerSidebarContainer = revisionHistoryContainer.querySelector('.revision-viewer-sidebar');
    revisionHistoryConfig.editorContainer = document.querySelector('.ck-editor-sidebar-wrapper');

    // Initialize plugin.
    const revisionHistoryPlugin = this.editor.plugins.get('RevisionHistory');
    const revisionTrackerPlugin = this.editor.plugins.get('RevisionTracker');
    const revisionHistoryElement = document.querySelector(this.storage.getSourceDataSelector('revisionHistory'));

    // Load revisions.
    const revisions = JSON.parse(revisionHistoryElement.value);
    for (const revision of revisions) {
      revisionHistoryPlugin.addRevisionData(revision);
    }

    // Hook to form submit.
    const form = this.editor.sourceElement.closest('form');
    form.addEventListener("submit", (e) => {
      this.updateStorage(revisionHistoryPlugin, revisionTrackerPlugin, revisionHistoryElement)
    });
  }

  updateStorage(plugin, tracker, storageElement) {
    tracker.update();
    // tracker.saveRevision( { name: 'Entity save' } );
    storageElement.value = JSON.stringify(plugin.getRevisions({
      toJSON: true
    }));
  }
}

export default RevisionHistoryAdapter;
