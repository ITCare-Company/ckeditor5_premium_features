(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["CKEditor5"] = factory();
	else
		root["CKEditor5"] = root["CKEditor5"] || {}, root["CKEditor5"]["revisionHistoryAdapter"] = factory();
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

;// CONCATENATED MODULE: ./modules/ckeditor5_premium_features_collaboration/js/ckeditor5_plugins/revisionHistoryAdapter/src/revisionHistoryAdapter.js


class RevisionHistoryAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new collaborationStorage(editor);
  }

  static get pluginName() {
    return 'RevisionHistoryAdapter'
  }

  static get requires() {
    return [ 'RevisionHistory' ]
  }

  init() {
    // Initialize revision history settings.
    const revisionHistoryConfig = this.editor.config._config.revisionHistory;
    let revisionHistoryContainer = document.querySelector(this.storage.getSourceDataSelector('revisionHistoryContainer'));
    if (revisionHistoryContainer === null) {
      revisionHistoryContainer = revisionHistoryConfig.viewerEditorElement.parentNode;
    }

    revisionHistoryConfig.viewerContainer = revisionHistoryContainer;
    revisionHistoryConfig.viewerEditorElement = revisionHistoryContainer.querySelector('.revision-viewer-editor');
    revisionHistoryConfig.viewerSidebarContainer = revisionHistoryContainer.querySelector('.revision-viewer-sidebar');
    revisionHistoryConfig.editorContainer = revisionHistoryContainer.querySelector('.editor-container');

    // Initialize plugin.
    const revisionHistoryPlugin = this.editor.plugins.get('RevisionHistory');
    // @todo not yet implemented.
    const revisionHistoryElement = document.querySelector(this.storage.getSourceDataSelector('revisionHistory'));

    // Load revisions.
    //const revisions = JSON.parse(revisionHistoryElement.value);
    const revisions = [];
    for (const revision of revisions) {
      revisionHistoryPlugin.addRevisionData(revision);
    }

    // Observe data change and update the data fields.
    this.editor.model.document.on( 'change:data', () => {
      this.updateStorage(revisionHistoryPlugin, revisionHistoryElement);
    });
  }

  updateStorage(plugin, storageElement) {
    storageElement.value = JSON.stringify(plugin.getRevisions({
      toJSON: true
    }));
  }
}

/* harmony default export */ const revisionHistoryAdapter = (RevisionHistoryAdapter);

;// CONCATENATED MODULE: ./modules/ckeditor5_premium_features_collaboration/js/ckeditor5_plugins/revisionHistoryAdapter/src/index.js
/**
 * @file The build process always expects an index.js file. Anything exported
 * here will be recognized by CKEditor 5 as an available plugin. Multiple
 * plugins can be exported in this one file.
 *
 * I.e. this file's purpose is to make plugin(s) discoverable.
 */
// cSpell:ignore simplebox



/* harmony default export */ const src = ({
  RevisionHistoryAdapter: revisionHistoryAdapter,
});

__webpack_exports__ = __webpack_exports__["default"];
/******/ 	return __webpack_exports__;
/******/ })()
;
});