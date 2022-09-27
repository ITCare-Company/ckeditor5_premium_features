import CollaborationStorage from "../../collaborationStorage/src/collaborationStorage";

class SidebarAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
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
    this.annotationsUIs = this.editor.plugins.get('AnnotationsUIs');
    const toolbar = this.editor.ui._toolbarConfig.items
    var self = this;

    console.log('after init 3');
    if (!this.sidebar || typeof this.sidebar == 'undefined') {
      return;
    }
    console.log('after init 23');

    if (!toolbar.includes('trackChanges') && !toolbar.includes('comment') || this.storage.isCollaborationDisabled()) {
      this.sidebarVisibilityModify(true);
    }
    else {
      this.sidebarVisibilityModify(false);
    }

    const sidebarMode = drupalSettings.ckeditor5SidebarMode ?? 'auto';
    if (sidebarMode === 'auto') {
      var doit;
      window.addEventListener('resize', function (event) {
        clearTimeout(doit);
        doit = setTimeout(function() {
          self.updateCkeditorMode();
        }, 100);
      });

      this.updateCkeditorMode();

      var toggle = document.getElementById("ck-sidebar-auto-toggle");

      if (!toggle.classList.contains('sidebar-toggle-init')) {
        toggle.classList.add('sidebar-toggle-init');

        toggle.addEventListener('click', function (event) {
          if (self.sidebar.classList.contains('narrowSidebar')) {
            self.sidebar.classList.remove('manual-toggled');
            self.setCkEditorSidebarMode('wideSidebar');
          }
          else {
            self.setCkEditorSidebarMode('narrowSidebar');
            self.sidebar.classList.add('manual-toggled');
          }
        });

      }
    }
    else {
      this.annotationsUIs.switchTo(sidebarMode);
    }
  }

  destroy() {
    this.sidebarVisibilityModify(true);
  }


  /**
   * Setup new sidebar mode.
   *
   * @param newMode
   *   Sidebar mode to setup.
   */
  setCkEditorSidebarMode = function(newMode) {
    if (!this.sidebar || typeof this.sidebar == 'undefined') {
      return;
    }
    if (this.sidebar.classList.contains('manual-toggled') && newMode == 'wideSidebar') {
      return;
    }

    this.sidebar.classList.remove('inline');
    this.sidebar.classList.remove('narrowSidebar');
    this.sidebar.classList.remove('wideSidebar');
    this.annotationsUIs.switchTo(newMode);
    this.sidebar.classList.add(newMode);
  }


  /**
   * Setup sidebar mode depends on resolution.
   */
  updateCkeditorMode = function() {
    // TODO: move to config?
    console.log('update sidebar mode ');
    let w = document.documentElement.clientWidth;
    if (w >= 1200) {
      this.setCkEditorSidebarMode('wideSidebar');
    }
    else if (w < 500) {
      this.setCkEditorSidebarMode('inline');
    }
    else if (w < 1200) {
      this.setCkEditorSidebarMode('narrowSidebar');
    }
  }
}


export default SidebarAdapter;
