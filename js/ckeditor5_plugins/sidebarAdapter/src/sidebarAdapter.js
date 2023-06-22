import CollaborationStorage from "../../collaborationStorage/src/collaborationStorage";

class SidebarAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
    this.toolbar = this.editor.ui._toolbarConfig.items
    this.sidebarMode = drupalSettings.ckeditor5SidebarMode ?? 'auto';
    this.resizeThreshold = 0;

    let sidebar_column = this.getSidebarWrapper(this.editor.sourceElement.id);

    if (typeof sidebar_column === 'undefined' || !sidebar_column) {
      return;
    }
    this.sidebarColumn = sidebar_column;
    this.sidebar = sidebar_column.parentElement;

    this.editor.config._config.sidebar = {
      container: sidebar_column,
    }
  }

  static get pluginName() {
    return 'SidebarAdapter'
  }

  sidebarVisibilityModify(hide = false) {
    if (!this.sidebar || typeof this.sidebar === 'undefined') {
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

    this.sidebarColumn.prepend(toggle);
  }

  afterInit() {
    if (!this.annotationsUIs || typeof this.annotationsUIs === "undefined" ||
      !this.sidebar || typeof this.sidebar === 'undefined') {
      return;
    }

    let sidebarHide = !this.toolbar.includes('trackChanges') && !this.toolbar.includes('comment') || this.storage.isCollaborationDisabled();

    this.sidebarVisibilityModify(sidebarHide);

    this.handleSidebarMode();

    this.checkIfInsideTab();
  }

  destroy() {
    this.sidebarVisibilityModify(true);
    let toggle = this.getSidebarToggle()
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
    let editorParent = this.storage.getEditorParentContainer(elementId);

    if (!editorParent) {
      return null;
    }

    return editorParent.querySelector('.ck-sidebar-wrapper');
  }

  /**
   * Checks sidebar mode setting and attaches event listeners if required.
   */
  handleSidebarMode() {
    let toggle = this.getSidebarToggle();

    if (this.sidebarMode !== 'auto') {
      this.setCkEditorSidebarMode(this.sidebarMode);
      if (toggle) {
        toggle.style.display = 'none';
      }
      return;
    }

    this.updateCkeditorMode();

    this.checkEditorLabel();

    if (!toggle) {
      return;
    }
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
   * Returns a toggle button for handled sidebar or null if not found.
   *
   * @returns {null|Element}
   */
  getSidebarToggle() {
    if (!this.sidebar || typeof this.sidebar === 'undefined') {
      return null;
    }
    return this.sidebar.querySelector(".ck-sidebar-auto-toggle");
  }

  /**
   * Setup new sidebar mode.
   *
   * @param newMode
   *   Sidebar mode to setup.
   */
  setCkEditorSidebarMode(newMode) {
    if (!this.sidebar || typeof this.sidebar === 'undefined') {
      return;
    }
    if (this.sidebar.classList.contains('manual-toggled') && newMode === 'wideSidebar') {
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
    // Check editor container width
    if (this.sidebar.clientWidth < 720) {
      newMode = this.sidebar.clientWidth >= 500 ? 'narrowSidebar' : 'inline'
    }
    this.setCkEditorSidebarMode(newMode);
  }

  /**
   * Check if there is a label right above the editor.
   */
  checkEditorLabel() {
    let label = this.sidebar.querySelector('label');
    if (label && typeof label !== 'undefined' && !label.classList.contains('visually-hidden')) {
      this.sidebarColumn.style.marginTop = label.clientHeight + "px";
    }
  }

  /**
   * Check if editor is inside the tab
   */
  checkIfInsideTab() {
    const tab = this.sidebar.closest('.field-group-tab');
    if (tab && typeof tab !== 'undefined') {
      this.checkParentTabs(tab)
    }
  }

  /**
   * Check if there are nested tabs
   * @param element
   */
  checkParentTabs(element) {
    const parent = element.parentElement.closest('.field-group-tab');

    if (parent && typeof parent !== 'undefined' && element !== parent) {
      // We have to check the display style and 'horizontal-tab-hidden' class to verify if the tab is
      // inside group of tabs.
      if (!parent.open || parent.style.display === "none" || parent.classList.contains('horizontal-tab-hidden')) {
        this.setObserverToElement(parent)
      } else {
        this.checkParentTabs(parent)
      }
    }
    // If the element is closed or contains horizontal-tab-hidden class then set observer.
    if (!element.open || element.classList.contains('horizontal-tab-hidden')) {
      this.setObserverToElement(element)
    }
  }

  /**
   * Set observer to tab and update editor when the tab is opened.
   * @param element
   */
  setObserverToElement(element) {
    this.setObserver(element).then(() => {
      this.updateCkeditorMode();
      this.checkEditorLabel();
    });
  }

  /**
   * Set observer
   * @param element
   * @returns {Promise<unknown>}
   */
  setObserver(element) {
    return new Promise(resolve => {
      const observer = new MutationObserver(mutations => {
        if (element.open && element.style.display !== 'none' && !element.classList.contains('horizontal-tab-hidden')) {
          resolve();
          observer.disconnect();
        }
      });
      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    });
  }

}


export default SidebarAdapter;
