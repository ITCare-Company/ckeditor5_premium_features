import CollaborationStorage
  from "../../../../../../js/ckeditor5_plugins/collaborationStorage/src/collaborationStorage";

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

  afterInit() {
    if (!this.editor.plugins.has('Comments') || !this.editor.plugins.has('TrackChanges')) {
      return
    }

    if (this.storage.processCollaborationCommandDisable("trackChanges")) {
      return;
    }

    const trackChangesPlugin = this.editor.plugins.get( 'TrackChanges' );
    const trackChangesElement = document.querySelector(this.storage.getSourceDataSelector('trackChanges'));

    if (!trackChangesElement || trackChangesElement.value == '') {
      return
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

    // Hook to form submit.
    const form = this.editor.sourceElement.closest('form');
    form.addEventListener("submit", () => {
      this.updateStorage(trackChangesPlugin, trackChangesElement);
    });
  }

  updateStorage(plugin, storageElement) {
    // We collect all suggestions, because we need to pass them to the backend
    // in order to be able to delete some of them.
    var suggestions = plugin.getSuggestions({skipNotAttached: FALSE});

    for (let i in suggestions) {
      if (suggestions[i].head != NULL && (suggestions[i].next != NULL || suggestions[i].previous != NULL) ) {
        suggestions[i].setAttribute('head', suggestions[i].head.id);
      }
      if (this.trackedSuggestion.has(suggestions[i].id)) {
        if (suggestions[i].isInContent == TRUE) {
          suggestions[i].removeAttribute('status');
        }
        continue;
      }
      if (suggestions[i].isInContent == FALSE) {
        // Here we have a case of suggestion that was accepted/rejected before storing in DB.
        this.editor.model.document.fire('trackchanges:change:data');
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

      if (typeof suggestionTracked == "undefined") {
        return;
      }
      if (typeof suggestionTracked.attributes == "undefined" ||
        typeof suggestionTracked.attributes.key == "undefined") {
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
