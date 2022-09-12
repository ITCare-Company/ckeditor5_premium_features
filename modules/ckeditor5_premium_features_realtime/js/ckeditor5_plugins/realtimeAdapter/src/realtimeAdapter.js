
class RealtimeAdapter {
  constructor( editor ) {
     this.editor = editor;
      const id_sidebar = this.editor.sourceElement.id + '-ck-sidebar';
       // TODO: Do we have some better way?
       this.editor.config._config.sidebar = {
         container: document.querySelector('#' + id_sidebar),
       }

       const editorId = this.editor.sourceElement.id;

       this.editor.config._config.collaboration = {
         channelId: drupalSettings.ckeditor5ChannelId + editorId,
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
    const collaborationConfig = this.editor.config._config.collaboration;
    const revisionHistoryConfig = this.editor.config._config.revisionHistory;

    const el = '#' + editorId + '-presence-list-container'
    presenceListConfig.container = document.querySelector( el );
    presenceListConfig.collapseAt = 8;
    revisionHistoryConfig.viewerContainer = document.querySelector( '.revision-history-container-data' ),

    revisionHistoryConfig.viewerEditorElement = document.querySelector('.revision-viewer-editor');
    revisionHistoryConfig.viewerSidebarContainer = document.querySelector('.revision-viewer-sidebar');
    revisionHistoryConfig.editorContainer = document.querySelector('.ck-editor-sidebar-wrapper');


    const annotationsUIs = this.editor.plugins.get('AnnotationsUIs');
    annotationsUIs.switchTo('wideSidebar');
    let cont = document.querySelector('.revision-viewer-sidebar');
    const class_wrapper = this.editor.sourceElement.id + '-ck-sidebar-wrapper .ck-sidebar-wrapper';
    const ck_sidebar_wrapper = document.querySelector('.' + class_wrapper);

    ck_sidebar_wrapper.classList.add('wideSidebar');
  }
}

export default RealtimeAdapter;
