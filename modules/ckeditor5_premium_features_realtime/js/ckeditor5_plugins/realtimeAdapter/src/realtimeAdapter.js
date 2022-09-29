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
  }

  static get pluginName() {
    return 'RealtimeAdapter'
  }

  init() {
    const presenceListPlugin = this.editor.plugins.get('PresenceList');
    const editorId = this.editor.sourceElement.id;
    const presenceListConfig = this.editor.config._config.presenceList;
    const editor = this.editor;
    const hasRTC = editor.plugins.has('RealTimeCollaborativeEditing');
    const hasSourceEditing = editor.plugins.has('SourceEditing');

    if (hasRTC && hasSourceEditing) {
      console.info('The Source editing plugin is not compatible with real-time collaboration, so it has been disabled. If you need it, please contact us to discuss your use case - https://ckeditor.com/contact/');
      editor.plugins.get('SourceEditing').forceDisabled('drupal-rtc');
    }

    const el = '#' + editorId + '-presence-list-container'
    if (!presenceListConfig.container) {
      presenceListConfig.container = document.querySelector(el);
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
  }

}

export default RealtimeAdapter;
