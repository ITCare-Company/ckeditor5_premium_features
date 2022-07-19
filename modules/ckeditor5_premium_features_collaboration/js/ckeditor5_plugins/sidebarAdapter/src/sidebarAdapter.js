import CollaborationStorage from "../../collaborationStorage/src/collaborationStorage";

class SidebarAdapter {
  constructor( editor ) {
    this.editor = editor;
    const id_sidebar = this.editor.sourceElement.id + '-ck-sidebar';
    this.sidebar = document.querySelector('#' + id_sidebar);

    // TODO: Do we have some better way?
    this.editor.config._config.sidebar = {
      container: this.sidebar,
    }

  }

  static get pluginName() {
    return 'SidebarAdapter'
  }

  static get requires() {
    // AnnotationsUIs is part of the comments repository.
    return [ 'CommentsRepository' ]
  }

  sidebarVisibilityModify(hide= false) {
    if (hide) {
      this.sidebar.classList.add('slider-off');
    } else {
      this.sidebar.classList.remove('slider-off');
    }
  }

  init() {
    const annotationsUIs = this.editor.plugins.get('AnnotationsUIs');
    const toolbar = this.editor.ui._toolbarConfig.items
    var self = this;

    if (!this.sidebar) {
      return;
    }

    if (!toolbar.includes('trackChanges') && !toolbar.includes('comment')) {
      this.sidebarVisibilityModify(true);
    }
    else {
      this.sidebarVisibilityModify(false);
    }

    const sidebarMode = drupalSettings.ckeditor5SidebarMode ?? 'auto';
    if (sidebarMode === 'auto') {
      window.addEventListener('resize', function (event) {
        updateCkeditorMode(self.sidebar, annotationsUIs);
      });
      updateCkeditorMode(self.sidebar, annotationsUIs)
    }
    else {
      annotationsUIs.switchTo(sidebarMode);
    }
  }

  destroy() {
    this.sidebarVisibilityModify(true);
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
