class DisableCollaborationMarkersInCaption {

  constructor( editor ) {
    this.editor = editor;
  }

  afterInit() {
    const editor = this.editor;
    const commentCommand = editor.commands.get( 'addCommentThread' );
    const trackChangesCommand = editor.commands.get( 'trackChanges' );

    let disabledCommands = false;

    if ( editor.plugins.has( 'DrupalImage' ) ) {
      const tcEditing = editor.plugins.get( 'TrackChangesEditing' );

      tcEditing.enableCommand( 'toggleImageCaption', ( executeCommand, options ) => {
        executeCommand( options );
      }, { priority: 'high' } );
    }

    editor.model.document.on( 'change', () => {
      const range = editor.model.document.selection.getFirstRange();
      const ancestor = range.getCommonAncestor();

      if ( ancestor.name == 'caption' ) {
        commentCommand.forceDisabled( 'drupal-premium-features' );
        trackChangesCommand.forceDisabled( 'drupal-premium-features' );
        trackChangesCommand.value = false;

        disabledCommands = true;
      } else {
        if ( disabledCommands ) {
          commentCommand.clearForceDisabled( 'drupal-premium-features' );
          trackChangesCommand.clearForceDisabled( 'drupal-premium-features' );

          disabledCommands = false;
        }
      }
    } );
  }
}

export default DisableCollaborationMarkersInCaption;
