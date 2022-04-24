import CollaborationStorage from "../../collaborationStorage";

class SidebarAdapter {
  constructor( editor ) {
    this.editor = editor;
    const id_sidebar = this.editor.sourceElement.id + '-ck-sidebar';
    // TODO: Do we have some better way?
    this.editor.config._config.sidebar = {
      container: document.querySelector('#' + id_sidebar),
    }

  }

  static get pluginName() {
    return 'SidebarAdapter'
  }

  static get requires() {
    // AnnotationsUIs is part of the comments repository.
    return [ 'CommentsRepository' ]
  }

  init() {
    const annotationsUIs = this.editor.plugins.get('AnnotationsUIs');
    const toolbar = this.editor.ui._toolbarConfig.items
    const class_wrapper = this.editor.sourceElement.id + '-ck-sidebar-wrapper .ck-sidebar-wrapper';
    const ck_sidebar_wrapper = document.querySelector('.' + class_wrapper);
    if (!toolbar.includes('trackChanges') && !toolbar.includes('comment')) {
      ck_sidebar_wrapper.classList.add('slider-off');
    }
    else {
      ck_sidebar_wrapper.classList.remove('slider-off');
    }

    const sidebarMode = drupalSettings.ckeditor5SidebarMode ?? 'auto';
    if (sidebarMode === 'auto') {
      window.addEventListener('resize', function (event) {
        updateCkeditorMode(ck_sidebar_wrapper, annotationsUIs);
      });
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
