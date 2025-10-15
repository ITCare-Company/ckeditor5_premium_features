/*
 * Copyright (c) 2003-2025, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

export default class importWordAdapter {

  constructor( editor ) {
    this.editor = editor;
  }

  init() {

    if (this.editor.plugins.has('ImageUploadEditing')) {
      const imageUploadEditing = this.editor.plugins.get('ImageUploadEditing');
      imageUploadEditing.on('uploadComplete', (evt, { data, imageElement }) => {
        this.editor.model.change((writer) => {
          console.log('imageElement', imageElement);
          console.log('evt', evt);

            // Set the selection on the image element.
            writer.setSelection( imageElement, 'on' );
            // Execute the command to change the style to 'block', which will convert it to imageBlock.
            this.editor.execute( 'imageStyle', { value: 'block' } );

          let htmlAttributes = imageElement._attrs.get('htmlImgAttributes');
          console.log('data', data);
          if (htmlAttributes === undefined) {
            return;
          }
          htmlAttributes.attributes.src = data.response.url;

          writer.setAttribute('style', htmlAttributes.styles, imageElement);
          console.log('htmlAttributes', htmlAttributes);
          writer.setAttribute('htmlImgAttributes', htmlAttributes, imageElement);

          //writer.setAttribute('src', data.response.url, imageElement);
          // writer.removeAttribute('htmlAttributes', imageElement);
          // writer.removeAttribute('htmlImgAttributes', imageElement);

        });
      });
    }
  }

  afterInit() {
    const mediaUploadConfig = this.editor.config._config.importWord.uploadMedia;
    if (mediaUploadConfig && mediaUploadConfig.enabled) {
      this.handleDataInsert();
    }
  }

  replace(documentDom, format) {
    let elements = documentDom.getElementsByTagName("img");
    let images = [];
    for (let image of elements ) {
      images.push(image);
    }

    for (let image of images ) {
      if (!this.isBase64ImageData(image.src)) {
        continue;
      }

      const uuid = this.uploadMediaFromBase64(image.src, format);
      if (uuid) {
        let drupalMedia = document.createElement('drupal-media')
        drupalMedia.setAttribute('data-entity-type', 'media');
        drupalMedia.setAttribute('data-entity-uuid', uuid);
        image.replaceWith(drupalMedia);
      }
    }

    return documentDom.innerHTML;
  }

  isBase64ImageData(str) {
    const regex = /^data:image\/[^;]+;base64,/;
    return regex.test(str);
  }

  handleDataInsert() {
    const importWordCommand = this.editor.commands.get('importWord');
    importWordCommand.on('dataInsert', (evt, data) => {
      const format = this.editor.sourceElement.dataset.editorActiveTextFormat
      const parser = new DOMParser();
      const documentDom = parser.parseFromString( data.html, 'text/html' ).body;
      data.html = this.replace(documentDom, format);
    },  { priority:'highest'});
  }

  uploadMediaFromBase64(imageContent, format) {
    const request = new XMLHttpRequest();
    const body = {
      image: imageContent
    }
    request.open("POST", "/ckeditor5-premium-features/import-word/upload-media/" + format + "/", false); // `false` makes the request synchronous
    request.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    request.send(JSON.stringify(body));
    if (request.status === 200) {
      const response = JSON.parse(request.responseText);
      if (response.mediaUuid) {
        return response.mediaUuid;
      }
    }
  }


  static get pluginName() {
    return 'importWordAdapter';
  }
}
