class DisableGhsTableIntegration {
  constructor( editor ) {
    this.editor = editor;
  }

  static get requires() {
    return [ 'DataFilter' ]
  }

  init() {
    const editor = this.editor;
    const dataFilter = editor.plugins.get( 'DataFilter' );

    dataFilter.on('register:table', (e) => {
      e.stop();
    }, { priority: 'high' });
  }
}

export default DisableGhsTableIntegration;
