
class RealtimeAdapter {
  constructor( editor ) {
     this.editor = editor;
      const id_sidebar = this.editor.sourceElement.id + '-ck-sidebar';
       // TODO: Do we have some better way?
       this.editor.config._config.sidebar = {
         container: document.querySelector('#' + id_sidebar),
       }

       this.editor.config._config.collaboration = {
         channelId: drupalSettings.ckeditor5ChannelId,
       }
  }

  static get pluginName() {
      return 'RealtimeAdapter'
    }

  static get requires() {
    // AnnotationsUIs is part of the comments repository.
    return [ 'CommentsRepository' ]
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
   * For the RTC it's the most suitable place to dynamically disable toolbar items.
   */
  afterInit() {
    this.processCollaborationCommandDisable("trackChanges");
    this.processCollaborationCommandDisable("addCommentThread");
  }

  /**
   * Checks if collaboration is set to be disabled and blocks the specified command (button).
   *
   * @param commandName
   *   Command name (related to a button)
   *
   * @returns {boolean}
   *   TRUE if command was blocked, FALSE otherwise.
   *
   * @todo: CCP-201 - refactor
   */
  processCollaborationCommandDisable(commandName) {
    if (!this.isCollaborationDisabled()) {
      return false;
    }

    const command = this.editor.commands._commands.get( commandName );

    if (typeof command == 'undefined') {
      return true;
    }

    command.forceDisabled( 'premium-features-module' );

    return true;
  }

  /**
   * Checks if collaboration is set to be disabled.
   *
   * @returns {boolean}
   *   TRUE if conditions for blocking collaboration are met, FALSE otherwise.
   *
   * @todo: CCP-201 - refactor
   */
  isCollaborationDisabled() {
    return typeof drupalSettings.ckeditor5Premium != 'undefined' &&
      typeof drupalSettings.ckeditor5Premium.disableCollaboration != "undefined" &&
      drupalSettings.ckeditor5Premium.disableCollaboration === true;
  }
}

export default RealtimeAdapter;
