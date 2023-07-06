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
