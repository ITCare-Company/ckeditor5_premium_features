(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["CKEditor5"] = factory();
	else
		root["CKEditor5"] = root["CKEditor5"] || {}, root["CKEditor5"]["trackChangesAdapter"] = factory();
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

;// CONCATENATED MODULE: ./modules/ckeditor5_premium_features_collaboration/js/ckeditor5_plugins/collaborationStorage.js
class CollaborationStorage {
  constructor( editor ) {
    this.editor = editor;
    this.elementId = this.editor.sourceElement.dataset.ckeditor5PremiumElementId;
  }

  getSourceDataSelector(type) {
    const types = {
      'trackChanges': '.track-changes',
      'comments': '.comments',
      'revisionHistory': '.revision-history',
      'revisionHistoryContainer': '.revision-history-container',
    };

    const cssClass = types[type] + '-data';
    const dataAttribute = `[data-ckeditor5-premium-element-id="${this.elementId}"]`;

    return cssClass + dataAttribute;
  }
}

/* harmony default export */ const collaborationStorage = (CollaborationStorage);

;// CONCATENATED MODULE: ./modules/ckeditor5_premium_features_collaboration/js/ckeditor5_plugins/trackChangesAdapter/src/trackChangesAdapter.js


class TrackChangesAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new collaborationStorage(editor);
  }

  static get pluginName() {
    return 'TrackChangesAdapter'
  }

  static get requires() {
    return [ 'TrackChanges' ]
  }

  init() {
    const trackChangesPlugin = this.editor.plugins.get( 'TrackChanges' );
    const trackChangesElement = document.querySelector(this.storage.getSourceDataSelector('trackChanges'));

    // Load suggestions.
    const suggestions = JSON.parse(trackChangesElement.value);
    for (const sugesstion of suggestions) {
      trackChangesPlugin.addSuggestion(sugesstion);
    }

    // Observe data change and update the data fields.
    this.editor.model.document.on( 'change:data', () => {
      this.updateStorage(trackChangesPlugin, trackChangesElement);
    });

    this.editor.model.document.on( 'comments:change:data', () => {
      this.updateStorage(trackChangesPlugin, trackChangesElement);
    });
  }

  updateStorage(plugin, storageElement) {
    storageElement.value = JSON.stringify(plugin.getSuggestions({
      skipNotAttached: true,
      toJSON: true
    }));
  }
}

/* harmony default export */ const trackChangesAdapter = (TrackChangesAdapter);

;// CONCATENATED MODULE: ./modules/ckeditor5_premium_features_collaboration/js/ckeditor5_plugins/trackChangesAdapter/src/index.js
/**
 * @file The build process always expects an index.js file. Anything exported
 * here will be recognized by CKEditor 5 as an available plugin. Multiple
 * plugins can be exported in this one file.
 *
 * I.e. this file's purpose is to make plugin(s) discoverable.
 */
// cSpell:ignore simplebox



/* harmony default export */ const src = ({
  TrackChangesAdapter: trackChangesAdapter,
});

__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});