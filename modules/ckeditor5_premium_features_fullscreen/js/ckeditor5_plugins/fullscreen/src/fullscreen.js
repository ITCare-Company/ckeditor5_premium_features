/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

import { Plugin } from 'ckeditor5/src/core';
import { ButtonView } from 'ckeditor5/src/ui';
import fullscreenIcon from './theme/icons/fullscreen.svg';

/* global document */

export default class FullScreen extends Plugin {

	static get pluginName() {
		return 'FullScreen';
	}

	init() {
		const editor = this.editor;
    let fullScrrenEnabled = false;

		editor.ui.componentFactory.add( 'FullScreen', locale => {
			const overlayClass = 'ck-fullscreen-overlay';
			const editorFullScreenClass = 'ck-fullscreen';
			const view = new ButtonView( locale );
      let defaultOffsetTop = null;
			// @todo keystroke
			view.set( {
				label: 'Maximize',
				icon: fullscreenIcon,
				tooltip: true,
				isToggleable: true
			} );

			view.on( 'execute', () => {
				const sideBarWrapper = editor.sourceElement.closest( '.ck-editor-sidebar-wrapper' );
				const sourceElementSibling = editor.sourceElement.nextElementSibling;
				const targetElement = sideBarWrapper ? sideBarWrapper : sourceElementSibling;
				const revHistoryElement = targetElement.parentNode.querySelector( '.revision-history-container-data' );
        const stickyPanel = targetElement.querySelector('.ck-sticky-panel__content');
        const stickyPanelPlaceholder = targetElement.querySelector('.ck-sticky-panel__placeholder');
				if ( document.body.classList.contains( overlayClass ) ) {
					targetElement.classList.remove( editorFullScreenClass );
					if ( revHistoryElement ) {
						revHistoryElement.classList.remove( editorFullScreenClass );
					}
					document.body.classList.remove( overlayClass );
					view.set( 'label', 'Maximize' );
					view.set( 'isOn', false );
          editor.ui.view.stickyPanel.set('isActive', true);
          stickyPanel.classList.remove('ck-sticky-panel__content_sticky');
          stickyPanel.removeAttribute('style');
          stickyPanelPlaceholder.style.display = 'none';
          editor.focus();
          fullScrrenEnabled = false;
          this.editor.ui.viewportOffset.top = defaultOffsetTop;
				}
				else {
					targetElement.classList.add( editorFullScreenClass );
					if ( revHistoryElement ) {
						revHistoryElement.classList.add( editorFullScreenClass );
					}
          document.body.classList.add( overlayClass );
					view.set( 'label', 'Minimize' );
					view.set( 'isOn', true );
          editor.ui.view.stickyPanel.set('isActive', false);
          editor.focus();
          fullScrrenEnabled = true;

          // viewportOffset.top can block display of balloon in case it's top property value is lower.
          // That happens because CKEditor limits possibility to display balloon to be within body element,
          // which can have padding added due to admin toolbar. In such case first line of fullscreen editor
          // can be positioned outside the body element.
          // We're fixing this issue by temporarily changing viewportOffset.top value for when fullscreen mode is active.
          defaultOffsetTop = this.editor.ui.viewportOffset.top;
          editor.ui.viewportOffset.top = 0;
				}
			} );
			return view;
		} );
    this.editor.ui.on('set', (eventInfo, name, value, oldValue) => {
      // Prevent changing viewportOffset.top value back to original value when viewport size is changed when fullscreen
      // mode is active.
      if (fullScrrenEnabled) {
        value.top = 0;
      }
    });
	}
}
