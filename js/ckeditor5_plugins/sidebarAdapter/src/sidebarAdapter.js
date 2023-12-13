/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

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
    this.editorContainer = this.sidebar.parentElement;

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
    let toggleWrapper = document.createElement('div');
    toggleWrapper.classList.add('ck-sidebar-auto-toggle-wrapper');
    let toggle = document.createElement('a');
    toggle.classList += 'ck-sidebar-auto-toggle ' + this.sidebarMode;
    toggle.id = 'ck-sidebar-auto-toggle';

    toggleWrapper.prepend(toggle);
    this.sidebarColumn.prepend(toggleWrapper);
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

    this.setSidebarObservers();

    this.editor.on('ready', () => {
      if (this.editor.ui.view.element) {
        this.editor.ui.view.element.classList += ' ck-sidebar-enabled';
      }
    });

  }

  destroy() {
    if (!this.annotationsUIs || typeof this.annotationsUIs === "undefined" ||
        !this.sidebar || typeof this.sidebar === 'undefined') {
      return;
    }

    this.intersectionObserver.disconnect();
    this.attrMutationObserver.disconnect();
    this.mutationObserver.disconnect();

    this.sidebarVisibilityModify(true);
    let toggle = this.getSidebarToggle()
    if (toggle) {
      toggle.remove();
    }
  }

  /**
   * Sets the Mutation and Intersection Observers which have the goal to adjust 'top' parameter to avoid intersection
   * of sidebar items with sidebar toggle and other items.
   */
  setSidebarObservers() {
    const root = this.sidebar.querySelector('.ck-sidebar')
    const mutationConfig = {
      childList: true
    };
    const intersectionConfig = {
      root: root,
      rootMargin: "0px",
      threshold: 0,
    };

    const attrMutationConfig = {
      attributes: true,
      attributeFilter: ['style']
    };
    this.attrMutationObserver = new MutationObserver((records, observer) => {
      records.forEach(record => {
        updateItemsTopPosition(record.target.closest('.ck-editor-sidebar-wrapper'));
      });
    });

    this.mutationObserver = new MutationObserver((records, observer) => {
      records.forEach(record => {
        record.addedNodes.forEach(addedNode => {
          this.intersectionObserver.observe(addedNode);
          this.attrMutationObserver.observe(addedNode, attrMutationConfig);
        });
        record.removedNodes.forEach(removedNode => {
          this.intersectionObserver.unobserve(removedNode);
        });
      });
    });
    this.mutationObserver.observe(root, mutationConfig);
    this.intersectionObserver = new IntersectionObserver(this.updateSidebarItemTop, intersectionConfig);
  }

  /**
   * Intersection Observer callback function. Sets the 'top' parameter value for sidebar item
   *
   * @param entries
   * @param observer
   */
  updateSidebarItemTop (entries, observer) {
    let sidebar = null;
    entries.forEach(entry => {
      sidebar = entry.target.closest('.ck-editor-sidebar-wrapper');
      // Handle the sidebar item which is intersecting with toggle.
      if (entry.isIntersecting) {
        const targetWrapper = entry.target;
        const wrapperHeight = parseFloat(targetWrapper.style.height);
        const threshold = wrapperHeight + 26;
        const currentTop = parseFloat(targetWrapper.style.top);
        if (currentTop < 0 && currentTop > -threshold) {
          targetWrapper.style.top = '-' + wrapperHeight + "px";
        }
        else if ((currentTop >= 0 && currentTop < 40) || !currentTop) {
          targetWrapper.style.top = '40px';
        }
      }

    });
    updateItemsTopPosition(sidebar);
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
    return this.sidebar.querySelector(".ck-sidebar-auto-toggle-wrapper");
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
      if (this.annotationsUIs.isActive('inline') || this.annotationsUIs.isActive('wideSidebar')) {
        newMode = 'narrowSidebar';
      } else {
        return;
      }
    }

    this.sidebar.classList.remove('inline', 'narrowSidebar', 'wideSidebar');
    this.annotationsUIs.switchTo(newMode);
    this.sidebar.classList.add(newMode);
  }

  /**
   * Setup sidebar mode depends on resolution.
   */
  updateCkeditorMode() {
    // TODO: move to config?
    let w = document.documentElement.clientWidth;
    let newMode = w >= 1200 ? 'wideSidebar' : (w >= 500 ? 'narrowSidebar' : 'inline');
    // Check editor container width
    if (this.editorContainer.clientWidth < 720) {
      newMode = this.editorContainer.clientWidth >= 500 ? 'narrowSidebar' : 'inline'
    }
    this.setCkEditorSidebarMode(newMode);
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

/**
 * Updates 'top' parameter value for intersecting sidebar items after first item was updated to avoid intersection with toggle.
 *
 * @param sidebar
 *    The sidebar container element.
 */
function updateItemsTopPosition (sidebar) {
  let sidebarItems = sidebar.querySelectorAll('.ck-sidebar > .ck-sidebar-item');
  let prevItem = null;
  const margin = sidebar.classList.contains('narrowSidebar') ? 5.0 : 25.0;

  for (const key in sidebarItems) {
    if (!sidebarItems[key].style) {
      continue;
    }
    const top = parseFloat(sidebarItems[key].style.top);

    // Skip elements with negative top.
    if (top < 0) {
      prevItem = sidebarItems[key];
      continue;
    }

    const prevTop = prevItem ? parseFloat(prevItem.style.top) : 0;
    const prevHeight = prevItem ? parseFloat(prevItem.offsetHeight) : 0;
    const expectedTop = prevTop > 0 ? prevTop + prevHeight + margin : 40;

    if (top < expectedTop) {
      sidebarItems[key].style.top = expectedTop + "px";
    }
    else if (top !== 40) {
      return;
    }

    prevItem = sidebarItems[key];
  }
}

export default SidebarAdapter;
