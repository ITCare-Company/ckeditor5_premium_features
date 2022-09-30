import CollaborationStorage from "../../collaborationStorage/src/collaborationStorage";

class SidebarAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
    this.toolbar = this.editor.ui._toolbarConfig.items
    this.sidebarMode = drupalSettings.ckeditor5SidebarMode ?? 'auto';
    this.resizeThreshold = 0;

    let sidebar_column = this.getSidebarWrapper(this.editor.sourceElement.id);

    if (typeof sidebar_column == 'undefined' || !sidebar_column) {
      return;
    }
    this.sidebarColumn = sidebar_column;
    this.sidebar = sidebar_column.parentElement;

    // TODO: Do we have some better way?
    this.editor.config._config.sidebar = {
      container: sidebar_column,
    }
  }

  static get pluginName() {
    return 'SidebarAdapter'
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
    if (!this.sidebar || !this.editor.plugins.has('AnnotationsUIs')) {
      return;
    }

    this.annotationsUIs = this.editor.plugins.get('AnnotationsUIs');
    let toggle = document.createElement('a');
    toggle.classList += 'ck-sidebar-auto-toggle ' + this.sidebarMode;
    toggle.id = 'ck-sidebar-auto-toggle';

    this.sidebarColumn.prepend(toggle);
  }

  afterInit() {
    if (!this.annotationsUIs || typeof this.annotationsUIs == "undefined" ||
      !this.sidebar || typeof this.sidebar == 'undefined') {
      return;
    }

    if (!this.toolbar.includes('trackChanges') && !this.toolbar.includes('comment') || this.storage.isCollaborationDisabled()) {
      this.sidebarVisibilityModify(true);
    }
    else {
      this.sidebarVisibilityModify(false);
    }

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
   * Search sidebar element near the element with provided ID.
   *
   * @param elementId
   *   Editor related tag ID.
   *
   * @returns {null|Element}
   *   Sidebar tag or NULL if tag not found.
   */
  getSidebarWrapper(elementId) {
    let sidebar_element = document.getElementById(elementId);

    while (sidebar_element && typeof sidebar_element !== "undefined"
    && typeof sidebar_element.classList !== "undefined" &&
    !sidebar_element.classList.contains('ck-editor-sidebar-wrapper')) {
      sidebar_element = sidebar_element.parentElement;
    }

    if (typeof sidebar_element === "undefined") {
      return null;
    }

    return sidebar_element.querySelector('.ck-sidebar-wrapper');
  }

  /**
   * Checks sidebar mode setting and attaches event listeners if required.
   */
  handleSidebarMode = function() {
    if (this.sidebarMode === 'auto') {
      this.updateCkeditorMode();

      var toggle = this.sidebar.querySelector(".ck-sidebar-auto-toggle");
      var self = this;

      window.addEventListener('resize', function () {
        clearTimeout(self.resizeThreshold);
        self.resizeThreshold = setTimeout(function() {
          self.updateCkeditorMode();
        }, 100);
      });

      toggle.addEventListener('click', function () {
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
    else {
      this.annotationsUIs.switchTo(this.sidebarMode);
    }
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
