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
    const annotationsUIs = this.editor.plugins.get('AnnotationsUIs');
    const sidebarMode = drupalSettings.ckeditor5SidebarMode ?? 'auto';
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

/**
 * Setup new sidebar mode.
 *
 * @param newMode
 *   Sidebar mode to setup.
 * @param ck_sidebar_wrapper
 *   JS sidebar object.
 * @param annotationsUIs
 *   AnnotationsUIs Plugin.
 */
function setCkEditorSidebarMode(newMode, ck_sidebar_wrapper, annotationsUIs) {
  ck_sidebar_wrapper.classList.remove('inline');
  ck_sidebar_wrapper.classList.remove('narrowSidebar');
  ck_sidebar_wrapper.classList.remove('wideSidebar');
  annotationsUIs.switchTo(newMode);
  ck_sidebar_wrapper.classList.add(newMode);
}

/**
 * Setup sidebar mode depends on resoltion.
 *
 * @param ck_sidebar_wrapper
 *   JS sidebar object.
 *
 * @param annotationsUIs
 *   AnnotationsUIs Plugin.
 */
function updateCkeditorMode(ck_sidebar_wrapper, annotationsUIs) {
  // TODO: move to config?
  let w = document.documentElement.clientWidth;
  if (w >= 1200) {
    setCkEditorSidebarMode('wideSidebar', ck_sidebar_wrapper, annotationsUIs);
  }
  else if (w < 500) {
    setCkEditorSidebarMode('inline', ck_sidebar_wrapper, annotationsUIs);
  }
  else if (w < 1200) {
    setCkEditorSidebarMode('narrowSidebar', ck_sidebar_wrapper, annotationsUIs);
  }
}

export default SidebarAdapter;
