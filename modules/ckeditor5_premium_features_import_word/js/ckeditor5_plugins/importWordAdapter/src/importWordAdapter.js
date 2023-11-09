/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

export default class importWordAdapter {

  constructor( editor ) {
    this.editor = editor;
  }

  init() {
    this.editor.on('ready', () => {
      const mediaUploadConfig = this.editor.config._config.importWord.uploadMedia;
      if (mediaUploadConfig && mediaUploadConfig.enabled) {
        this.handleDataInsert();
      }
    });

    if (this.editor.plugins.has('ImageUploadEditing')) {
      const imageUploadEditing = this.editor.plugins.get('ImageUploadEditing');
      imageUploadEditing.on('uploadComplete', (evt, { imageElement }) => {
        this.editor.model.change((writer) => {
          writer.removeAttribute('htmlAttributes', imageElement);
        });
      });
    }
  }

  async replace(documentDom, format) {
    return new Promise(async resolve => {
      let elements = documentDom.getElementsByTagName("img");
      let images = [];
      for (let image of elements ) {
        images.push(image);
      }
      // let requests = [];
      for (let image of images ) {
        if (!this.isBase64ImageData(image.src)) {
          continue;
        }
        let drupalMedia = document.createElement('drupal-media')
        drupalMedia.setAttribute('data-entity-type', 'media');
        // requests.push(Drupal.CKEditor5PremiumFeaturesImportWord.uploadMediaFromBase64(image.src, format).then((result) => {
        //     drupalMedia.setAttribute('data-entity-uuid', result);
        //     image.replaceWith(drupalMedia);
        //   }
        // ));
        await Drupal.CKEditor5PremiumFeaturesImportWord.uploadMediaFromBase64(image.src, format).then((result) => {
            drupalMedia.setAttribute('data-entity-uuid', result);
            image.replaceWith(drupalMedia);
          }
        )
      }
      // Promise.all(requests).then(() => { console.log('resolve all'); resolve(documentDom.innerHTML); })
      resolve(documentDom.innerHTML)
    })
  }

  isBase64ImageData(str) {
    const regex = /^data:image\/[^;]+;base64,/;
    return regex.test(str);
  }

  async handleDataInsert() {
    const importWordCommand = this.editor.commands.get('importWord');
    let asyncData = null;
    importWordCommand.on('dataInsert', async (evt, data) => {
      if (!asyncData) {
        evt.stop();
        const format = this.editor.sourceElement.dataset.editorActiveTextFormat
        const parser = new DOMParser();
        const documentDom = parser.parseFromString( data.html, 'text/html' ).body;
        asyncData = await this.replace(documentDom, format);
        data.html = asyncData;
        //Temporary solution for development, has to be change
        importWordCommand._prepareForImport();
        importWordCommand.set('isBusy', false);
        importWordCommand.fire('dataInsert', data);
        asyncData = null;
      }
    },  { priority:'highest'});
  }


  static get pluginName() {
    return 'importWordAdapter';
  }
}
