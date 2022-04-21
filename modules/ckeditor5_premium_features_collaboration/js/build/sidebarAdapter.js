(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["CKEditor5"] = factory();
	else
		root["CKEditor5"] = root["CKEditor5"] || {}, root["CKEditor5"]["sidebarAdapter"] = factory();
})(self, function() {
return /******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": () => (/* binding */ src)
});

;// CONCATENATED MODULE: ./modules/ckeditor5_premium_features_collaboration/js/ckeditor5_plugins/sidebarAdapter/src/sidebarAdapter.js


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

/* harmony default export */ const sidebarAdapter = (SidebarAdapter);

;// CONCATENATED MODULE: ./modules/ckeditor5_premium_features_collaboration/js/ckeditor5_plugins/sidebarAdapter/src/index.js
/**
 * @file The build process always expects an index.js file. Anything exported
 * here will be recognized by CKEditor 5 as an available plugin. Multiple
 * plugins can be exported in this one file.
 *
 * I.e. this file's purpose is to make plugin(s) discoverable.
 */
// cSpell:ignore simplebox



/* harmony default export */ const src = ({
  SidebarAdapter: sidebarAdapter,
});

__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});