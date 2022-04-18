import CollaborationStorage from "../../collaborationStorage";

class SidebarAdapter {
  constructor( editor ) {
    this.editor = editor;
    let id_sidebar = this.editor.sourceElement.id + '-ck-sidebar';
    this.editor.config._config.sidebar = {
      container: document.querySelector('#' + id_sidebar),
    }

  }

  static get pluginName() {
    return 'SidebarAdapter'
  }

  init() {
    const annotationsUIs = this.editor.plugins.get( 'AnnotationsUIs' );
    const sidebarMode = drupalSettings.ckeditor5SidebarMode ?? 'inline';
    if (sidebarMode === 'auto') {
      const ck_sidebar_wrapper = document.querySelector('.ck-sidebar-wrapper');
      window.onresize = function () {
        updateCkeditorMode(ck_sidebar_wrapper, annotationsUIs);
      }
      updateCkeditorMode(ck_sidebar_wrapper, annotationsUIs)
    }
    else {
      annotationsUIs.switchTo(sidebarMode);
    }
  }

}

function updateCkEditorSidebarMode(newMode, ck_sidebar_wrapper, annotationsUIs) {
  ck_sidebar_wrapper.classList.remove('inline');
  ck_sidebar_wrapper.classList.remove('narrowSidebar');
  ck_sidebar_wrapper.classList.remove('wideSidebar');
  annotationsUIs.switchTo(newMode);
  ck_sidebar_wrapper.classList.add(newMode);
}

function updateCkeditorMode(ck_sidebar_wrapper , annotationsUIs) {
  let w = document.documentElement.clientWidth;
  if (w >= 1200) {
    updateCkEditorSidebarMode('wideSidebar', ck_sidebar_wrapper, annotationsUIs);
  }
  else if(w < 500) {
    updateCkEditorSidebarMode('inline', ck_sidebar_wrapper , annotationsUIs);
  }
  else if(w < 1199) {
    updateCkEditorSidebarMode('narrowSidebar', ck_sidebar_wrapper, annotationsUIs);
  }
}

export default SidebarAdapter;
