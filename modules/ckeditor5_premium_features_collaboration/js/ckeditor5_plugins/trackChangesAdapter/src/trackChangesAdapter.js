import UserAdapter from "../../userAdapter/src/userAdapter";

class TrackChangesAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.elementId = this.editor.sourceElement.dataset.ckeditor5PremiumElementId;
  }

  static get pluginName() {
    return 'TrackChangesAdapter'
  }

  init() {
    // Initialize the user adapter.
    new UserAdapter(this.editor).init();

    const trackChangesPlugin = this.editor.plugins.get( 'TrackChanges' );
    const trackChangesElement = document.querySelector(this.getSourceDataSelector());

    // Load suggestions.
    const suggestions = JSON.parse(trackChangesElement.value);
    for (const sugesstion of suggestions) {
      trackChangesPlugin.addSuggestion((sugesstion));
    }

    // Observe data change and update the data fields.
    this.editor.model.document.on( 'change:data', () => {
      trackChangesElement.value = JSON.stringify(trackChangesPlugin.getSuggestions({
        skipNotAttached: true,
        toJSON: true
      }));
    });

    // @todo move the Suggestions adapter logic here.
  }

  getSourceDataSelector() {
    const cssClass = '.track-changes-data';
    const dataAttribute = `[data-ckeditor5-premium-element-id="${this.elementId}"]`;

    return cssClass + dataAttribute;
  }
}

export default TrackChangesAdapter;
