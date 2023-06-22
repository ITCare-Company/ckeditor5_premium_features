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

		editor.ui.componentFactory.add( 'FullScreen', locale => {
			const overlayClass = 'ck-fullscreen-overlay';
			const editorFullScreenClass = 'ck-fullscreen';
			const view = new ButtonView( locale );
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
				if ( document.body.classList.contains( overlayClass ) ) {
					targetElement.classList.remove( editorFullScreenClass );
					if ( revHistoryElement ) {
						revHistoryElement.classList.remove( editorFullScreenClass );
					}
					document.body.classList.remove( overlayClass );
					view.set( 'label', 'Maximize' );
					view.set( 'isOn', false );
				}
				else {
					targetElement.classList.add( editorFullScreenClass );
					if ( revHistoryElement ) {
						revHistoryElement.classList.add( editorFullScreenClass );
					}
					document.body.classList.add( overlayClass );
					view.set( 'label', 'Minimize' );
					view.set( 'isOn', true );
				}
			} );
			return view;
		} );
	}
}
