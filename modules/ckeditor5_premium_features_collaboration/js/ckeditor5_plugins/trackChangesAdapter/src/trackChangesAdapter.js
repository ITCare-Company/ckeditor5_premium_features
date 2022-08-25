import CollaborationStorage from "../../collaborationStorage/src/collaborationStorage";

class TrackChangesAdapter {
  trackedSuggestion;

  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
  }

  static get pluginName() {
    return 'TrackChangesAdapter'
  }

  static get requires() {
    return [ 'TrackChanges', 'Comments', 'TrackChangesAdapter' ]
  }

  init() {
    if (!this.editor.plugins.has('Comments') || !this.editor.plugins.has('TrackChanges')) {
      return
    }

    const trackChangesPlugin = this.editor.plugins.get( 'TrackChanges' );
    const trackChangesElement = document.querySelector(this.storage.getSourceDataSelector('trackChanges'));

    if (!trackChangesElement) {
      return;
    }
    this.trackedSuggestion = new Map();

    // Load suggestions.
    const suggestions = JSON.parse(trackChangesElement.value);
    for (const suggestion of suggestions) {
      trackChangesPlugin.addSuggestion(suggestion);
      this.attachSuggestionEvents(trackChangesPlugin.getSuggestion(suggestion.id));
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
    // We collect all suggestions, because we need to pass them to the backend
    // in order to be able to delete some of them.
    var suggestions = plugin.getSuggestions({skipNotAttached: false});

    for (let i in suggestions) {
      if (this.trackedSuggestion.has(suggestions[i].id)) {
        continue;
      }
      if (suggestions[i].isInContent == false) {
        // Here we have a case of suggestion that was accepted/rejected before storing in DB.
        continue;
      }

      this.attachSuggestionEvents(suggestions[i]);
    }

    storageElement.value = JSON.stringify(Array.from(this.trackedSuggestion.values()));
  };

  attachSuggestionEvents(suggestion) {
    this.trackedSuggestion.set(suggestion.id, suggestion);
    var self = this;

    var suggestionStatusUpdate = function (event) {
      let suggestionTracked = self.trackedSuggestion.get(event.source.id);
      if (typeof suggestionTracked.attributes.key == "undefined") {
        self.trackedSuggestion.delete(event.source.id);
        return;
      }
      suggestionTracked.setAttribute('status', event.name);
    }

    suggestion.on('accept', suggestionStatusUpdate);
    suggestion.on('discard', suggestionStatusUpdate);
  }
}

export default TrackChangesAdapter;
