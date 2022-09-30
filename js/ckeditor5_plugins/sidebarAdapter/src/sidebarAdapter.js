import CollaborationStorage from "../../collaborationStorage/src/collaborationStorage";

class SidebarAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
    this.toolbar = this.editor.ui._toolbarConfig.items
    this.sidebarMode = drupalSettings.ckeditor5SidebarMode ?? 'auto';
    this.resizeThreshold = 0;

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

  sidebarVisibilityModify(hide= false) {
    if (!this.sidebar || typeof this.sidebar == 'undefined') {
      return;
    }
    this.sidebar.classList.toggle('slider-off', hide);
  }

  init() {
    if (!this.sidebar || !this.editor.plugins.has('AnnotationsUIs')) {
      return;
    }

    this.annotationsUIs = this.editor.plugins.get('AnnotationsUIs');
    let toggle = document.createElement('a');
    toggle.classList += 'ck-sidebar-auto-toggle ' + this.sidebarMode;
    toggle.id = 'ck-sidebar-auto-toggle';

    this.sidebar.prepend(toggle);
  }

  afterInit() {
    if (!this.annotationsUIs || typeof this.annotationsUIs == "undefined" ||
      !this.sidebar || typeof this.sidebar == 'undefined') {
      return;
    }

    let sidebarHide = !this.toolbar.includes('trackChanges') && !this.toolbar.includes('comment') || this.storage.isCollaborationDisabled();

    this.sidebarVisibilityModify(sidebarHide);

    this.handleSidebarMode();
  }

  destroy() {
    this.sidebarVisibilityModify(true);
    let toggle = document.getElementById("ck-sidebar-auto-toggle");
    if (toggle) {
      toggle.remove();
    }
  }

  /**
   * Checks sidebar mode setting and attaches event listeners if required.
   */
  handleSidebarMode = function() {
    if (this.sidebarMode !== 'auto') {
      this.annotationsUIs.switchTo(this.sidebarMode);
      return;
    }

    this.updateCkeditorMode();

    let toggle = document.getElementById("ck-sidebar-auto-toggle");

    window.addEventListener('resize', () => {
      clearTimeout(this.resizeThreshold);
      this.resizeThreshold = setTimeout(() => {
        this.updateCkeditorMode();
      }, 100);
    });

    toggle.addEventListener('click', () => {
      if (this.sidebar.classList.contains('narrowSidebar')) {
        this.sidebar.classList.remove('manual-toggled');
        this.setCkEditorSidebarMode('wideSidebar');
      }
      else {
        this.setCkEditorSidebarMode('narrowSidebar');
        this.sidebar.classList.add('manual-toggled');
      }
    });
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

    this.sidebar.classList.remove('inline', 'narrowSidebar', 'wideSidebar');
    this.annotationsUIs.switchTo(newMode);
    this.sidebar.classList.add(newMode);
  }

  /**
   * Setup sidebar mode depends on resolution.
   */
  updateCkeditorMode = function() {
    // TODO: move to config?
    let w = document.documentElement.clientWidth;
    let newMode = w >= 1200 ? 'wideSidebar' : (w >= 500 ? 'narrowSidebar' : 'inline');

    this.setCkEditorSidebarMode(newMode);
  }
}


export default SidebarAdapter;
