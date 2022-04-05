import CollaborationStorage from "./collaborationStorage";

class TrackChangesAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
  }

  static get pluginName() {
    return 'TrackChangesAdapter'
  }

  init() {
    const trackChangesPlugin = this.editor.plugins.get( 'TrackChanges' );
    const trackChangesElement = document.querySelector(this.storage.getSourceDataSelector('trackChanges'));

    // Load suggestions.
    const suggestions = JSON.parse(trackChangesElement.value);
    for (const sugesstion of suggestions) {
      trackChangesPlugin.addSuggestion(sugesstion);
    }

    // Observe data change and update the data fields.
    this.editor.model.document.on( 'change:data', () => {
      this.updateStorage(trackChangesPlugin, trackChangesElement);
    });

    this.editor.model.document.on( 'comments:change:data', () => {
      this.updateStorage(trackChangesPlugin, trackChangesElement);
    });
  }

  updateStorage(plugin, storageElement) {
    storageElement.value = JSON.stringify(plugin.getSuggestions({
      skipNotAttached: true,
      toJSON: true
    }));
  }
}

export default TrackChangesAdapter;
