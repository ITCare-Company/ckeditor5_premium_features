/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

const { View } = window.CKEditor5.ui;
const { Rect } = window.CKEditor5.utils;
const { Plugin } = window.CKEditor5.core;

const ERROR_NOTIFICATION_DEFINITION = {
  header: 'Oops...',
  description: 'It seems that the editor encountered an error. Check the browser\'s console for more details.',
  type: 'error'
}

class ErrorNotifications extends Plugin {
  constructor( ...args ) {
    super( ...args );

    this.errorNotificationView = null;
  }

  static get pluginName() {
    return 'ErrorNotifications'
  }

  init() {
    const editor = this.editor;

    this.errorNotificationView = new NotificationView( editor.locale, ERROR_NOTIFICATION_DEFINITION )

    this.set( '_editable', null );

    this.errorNotificationView.bind( '_editable' ).to( this, '_editable' );

    editor.ui.once( 'ready', () => this.set( '_editable', editor.ui.view.editable.element ) );

    editor.ui.view.main.add( this.errorNotificationView );

    this._attachListeners();

    this.errorNotificationView.on( 'closeNotification', evt => {
      this.errorNotificationView.hide();

      editor.editing.view.focus();
    } );
  }

  afterInit() {
    const editor = this.editor;

    setTimeout( () => {
      editor.model.change( writer => {
        writer.insertElement( 'paragraph', editor.model.document.getRoot(), 'before')
      })
    }, 2000 )

    setTimeout( () => {
      editor.model.change( writer => {
        writer.insertElement( 'paragraph', editor.model.document.getRoot(), 'before')
      })
    }, 5000 )
  }

  destroy() {
    this._detachListeners();

    super.destroy();
  }

  _attachListeners() {
    window.addEventListener( 'error', this._handleError.bind( this ) );
    window.addEventListener( 'unhandledrejection', this._handleError.bind( this ) );
  }

  _detachListeners() {
    window.removeEventListener( 'error', this._handleError.bind( this ) );
    window.removeEventListener( 'unhandledrejection', this._handleError.bind( this ) );
  }

  _handleError( { error } ) {
    const { name } = error;

    if ( name && !name.includes( 'CKEditorError' ) ) {
      return;
    }

    if ( this.errorNotificationView.isVisible ) {
      return;
    }

    this.errorNotificationView.show();
  }
}

class NotificationView extends View {
  constructor( locale, definition ) {
    super( locale );

    this.closeNotificationButton = null;

    this.set( '_editable', null );
    this.set( 'isVisible', false );
    this.set( 'positionBottom', '20px' );
    this.set( 'positionRight', '15px' );

    this.createTemplate( definition );

    this.render();

    this.on( 'change:isVisible', () => this._updateNotificationPosition() )

    this.listenTo( global.document, 'scroll', ( evt, data ) => {
      if ( this.isVisible ) {
        this._updateNotificationPosition();
      }
    } );
  }

  createTemplate( definition ) {
    const bind = this.bindTemplate;

    const notificationHeader = this._createNotificationHeader( definition.header, definition.type );
    const notificationDescription = this._createNotificationDescription( definition.description );
    const closeNotificationButton = this._createCloseNotificationButton();

    this.setTemplate( {
      tag: 'div',
      attributes: {
        class: [
          'ck-notification',
          `ck-notification__${ definition.type }`,
          bind.if( 'isVisible', 'ck-hidden', value => !value  )
        ],
        style: {
          position: 'absolute',
          bottom: bind.to( 'positionBottom' ),
          right: bind.to( 'positionRight' ),
          'z-index': 99999
        }
      },
      children: [
        notificationHeader,
        notificationDescription,
        closeNotificationButton
      ]
    } )
  }

  show() {
    this.isVisible = true;
  }

  hide() {
    this.isVisible = false;
  }

  _createNotificationHeader( text, type ) {
    const view = new View();

    view.setTemplate( {
      tag: 'h4',
      attributes: {
        class: [
          'ck-notification__header',
          `ck-notification__header-${ type }`
        ]
      },
      children: [ text ]
    } )

    return view;
  }

  _createNotificationDescription( text ) {
    const view = new View();

    view.setTemplate( {
      tag: 'p',
      attributes: {
        class: [
          'ck-notification__description'
        ]
      },
      children: [ text ]
    } )

    return view;
  }

  _createCloseNotificationButton() {
    const view = new View();

    const bind = view.bindTemplate;

    view.setTemplate( {
      tag: 'span',
      attributes: {
        class: [
          'ck-notification__close'
        ]
      },
      children: [ 'x' ],
      on: {
        click: bind.to( evt => this.fire( 'closeNotification' ) )
      }
    } )

    return view;
  }

  _updateNotificationPosition() {
    const editable = this._editable;

    if ( !editable ) {
      return;
    }

    const editableRect = new Rect( editable );

    const visibleRect = editableRect.getVisible();

    const viewportHeight = window.innerHeight;
    const bottomBoundary = visibleRect.bottom - viewportHeight;
    const topBoundary = visibleRect.top;

    // Prevent sticking out of the editable.
    // viewportHeight - (notification height + margin)
    if ( topBoundary >= viewportHeight - 100 ) {
      return;
    }

    // If the editable's bottom boundary is invisible, stick the notification
    // to the viewport's position in the editable.
    if ( visibleRect.bottom > viewportHeight ) {
      this.set( 'positionBottom', `${ bottomBoundary + 20 }px` );
    } else {
      this.set( 'positionBottom', '20px' );
    }
  }
}

export default ErrorNotifications;
