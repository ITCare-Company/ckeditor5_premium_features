import CollaborationStorage from "../../collaborationStorage";

class RevisionHistoryAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
  }

  static get pluginName() {
    return 'RevisionHistoryAdapter'
  }

  init() {
    const revisionHistoryPlugin = this.editor.plugins.get('RevisionHistory');
    // @todo not yet implemented.
    const revisionHistoryElement = document.querySelector(this.storage.getSourceDataSelector('revisionHistory'));

    // Load revisions.
    // const revisions = JSON.parse(revisionHistoryElement.value);
    const revisions = [];
    for (const revision of revisions) {
      revisionHistoryPlugin.addRevisionData(revision);
    }

    // Observe data change and update the data fields.
    this.editor.model.document.on( 'change:data', () => {
      this.updateStorage(revisionHistoryPlugin, revisionHistoryElement);
    });
  }

  updateStorage(plugin, storageElement) {
    storageElement.value = JSON.stringify(plugin.getRevisions({
      toJSON: true
    }));
  }
}

export default TrackChangesAdapter;
