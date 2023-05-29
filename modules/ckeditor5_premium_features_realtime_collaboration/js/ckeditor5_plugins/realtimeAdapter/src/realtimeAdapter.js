import CollaborationStorage
  from "../../../../../../js/ckeditor5_plugins/collaborationStorage/src/collaborationStorage";

class RealtimeAdapter {
  constructor(editor) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);

    if (typeof drupalSettings.ckeditor5ChannelId == "undefined" ||
      typeof this.editor.sourceElement.dataset.ckeditorfieldid == "undefined" ||
      typeof drupalSettings.ckeditor5ChannelId[this.editor.sourceElement.dataset.ckeditorfieldid] == "undefined") {
      return;
    }
    this.editor.config._config.collaboration = {
      channelId: drupalSettings.ckeditor5ChannelId[this.editor.sourceElement.dataset.ckeditorfieldid],
    }
    this.setPresenceListContainer();
  }

  static get pluginName() {
    return 'RealtimeAdapter'
  }

  init() {
    const editor = this.editor;
    const hasRTC = editor.plugins.has('RealTimeCollaborativeEditing');
    const hasSourceEditing = editor.plugins.has('SourceEditing');
    if (hasRTC && hasSourceEditing) {
      console.info('The Source editing plugin is not compatible with real-time collaboration, so it has been disabled. If you need it, please contact us to discuss your use case - https://ckeditor.com/contact/');
      editor.plugins.get('SourceEditing').forceDisabled('drupal-rtc');
    }
  }

  setPresenceListContainer() {
    const presenceListConfig = this.editor.config._config.presenceList;
    if (!presenceListConfig || typeof presenceListConfig === "undefined") {
      return;
    }

    if (!presenceListConfig.container) {
      let editorParent = this.storage.getEditorParentContainer(this.editor.sourceElement.id)
      if (editorParent !== null) {
        presenceListConfig.container = editorParent.querySelector('.ck-presence-list-container')
      }
    }
    if (!presenceListConfig.collapseAt) {
      presenceListConfig.collapseAt = drupalSettings.presenceListCollapseAt;
    }
  }

  /**
   * Executed after plugin is initialized.
   *
   * For the RTC it's the most suitable place to dynamically disable toolbar
   * items.
   */
  afterInit() {
    this.storage.processCollaborationCommandDisable("trackChanges");
    this.storage.processCollaborationCommandDisable("addCommentThread");
    this.checkIfInitialDataChanged();

    this.editor.on('ready', () => {
      let textFormat = this.editor.sourceElement.dataset.editorActiveTextFormat;
      let isTrackingChangesOn = drupalSettings.ckeditor5Premium.tracking_changes.default_state;
      if (typeof isTrackingChangesOn[textFormat] !== 'undefined' && isTrackingChangesOn[textFormat]) {
        this.editor.execute('trackChanges');
      }
    });
  }

  /**
   *  Check if the editor's initial data is different from the data from CS.
   *  If so, set "data-editor-value-is-changed" attribute to TRUE.
   */
  checkIfInitialDataChanged() {
    const initialData = this.editor.config._config.initialData;
    this.editor.on('ready', () => {
      if (initialData !== this.editor.getData()) {
        this.editor.sourceElement.setAttribute('data-editor-value-is-changed', true);
      }
    } );
  }

}

export default RealtimeAdapter;
