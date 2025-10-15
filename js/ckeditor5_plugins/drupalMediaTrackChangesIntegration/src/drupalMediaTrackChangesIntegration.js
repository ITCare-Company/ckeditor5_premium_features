/*
 * Copyright (c) 2003-2025, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

import { Plugin } from 'ckeditor5/src/core';

class DrupalMediaTrackChangesIntegration extends Plugin {

  static get pluginName() {
    return 'DrupalMediaTrackChangesIntegration';
  }

  afterInit() {
    const editor = this.editor;

    if (!editor.plugins.has( 'TrackChangesEditing' )) {
      return;
    }

    const trackChangesEditing = editor.plugins.get( 'TrackChangesEditing' );

    trackChangesEditing.enableCommand( 'insertDrupalMedia' );

    const t = editor.t;
    const descriptionFactory = typeof trackChangesEditing._descriptionFactory === 'undefined' ? trackChangesEditing.descriptionFactory : trackChangesEditing._descriptionFactory;
    descriptionFactory.registerElementLabel(
      'drupalMedia',

      quantity => t( {
        string: 'drupal media',
        plural: '%0 drupal medias',
        id: 'ELEMENT_DRUPAL_MEDIA'
      }, quantity )
    );


    editor.editing.mapper.on( 'modelToViewPosition', ( evt, data ) => {
      if ( data.isPhantom ) {
        return;
      }
      return;
      console.log('evt', evt);
      console.log('data', data);

      const modelPosition = data.modelPosition;
      const parent = modelPosition.parent; // text node parent

      console.log('modelPosition', modelPosition);
      console.log('parent', parent);
      console.log('isValid', modelPosition.isValid());

      if ( parent && parent.name === 'caption' ) {

        let viewElement = data.mapper.toViewElement( parent );
        while ( viewElement && viewElement.name !== 'figcaption' ) {
          viewElement = viewElement.nextSibling;
        }
        console.log('viewElement', viewElement);
        const viewPosition = editor.editing.view.createPositionAt( viewElement,  modelPosition.offset );
        console.log('viewPosition', viewPosition);
        data.viewPosition = viewPosition;
        evt.stop();
      }
    });
  }
}

export default DrupalMediaTrackChangesIntegration;
