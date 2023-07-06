/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

export default class importWordAdapter {

  constructor( editor ) {
    this.editor = editor;
  }

  init() {
    if (this.editor.plugins.has('ImageUploadEditing')) {
      const imageUploadEditing = this.editor.plugins.get('ImageUploadEditing');
      imageUploadEditing.on('uploadComplete', (evt, { imageElement }) => {
        this.editor.model.change((writer) => {
          writer.removeAttribute('htmlAttributes', imageElement);
        });
      });
    }
  }

  static get pluginName() {
    return 'importWordAdapter';
  }
}
