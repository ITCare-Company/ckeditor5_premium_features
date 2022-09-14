class SidebarAdapter {
  constructor( editor ) {
    this.editor = editor;
    const id_sidebar = this.editor.sourceElement.id + '-ck-sidebar';
    let sidebar_wrapper = document.querySelector('#' + id_sidebar);

    if (typeof sidebar_wrapper == 'undefined' || !sidebar_wrapper) {
      return;
    }
    this.sidebar = sidebar_wrapper;

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
    return [ 'CommentsRepository', 'AnnotationsUIs' ]
  }

  sidebarVisibilityModify(hide= false) {
    if (!this.sidebar || typeof this.sidebar == 'undefined') {
      return;
    }
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

    if (!this.sidebar || typeof this.sidebar == 'undefined') {
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
      var toggle = document.getElementById("ck-sidebar-auto-toggle");
      toggle.addEventListener('click', function (event) {
        if (self.sidebar.classList.contains('narrowSidebar')) {
          setCkEditorSidebarMode('wideSidebar', self.sidebar, annotationsUIs);
        }
        else {
          setCkEditorSidebarMode('narrowSidebar', self.sidebar, annotationsUIs);
        }
      });
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
  if (!ck_sidebar_wrapper || typeof ck_sidebar_wrapper == 'undefined') {
    return;
  }
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
