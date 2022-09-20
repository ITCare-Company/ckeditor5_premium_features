class DisableRealtimeMarkersInCaption {

  constructor( editor ) {
    this.editor = editor;
  }

  afterInit() {
    const editor = this.editor;
    const commentCommand = editor.commands.get( 'addCommentThread' );
    const trackChangesCommand = editor.commands.get( 'trackChanges' );

    editor.set( 'disabledCommands', false );

    if ( editor.plugins.has( 'DrupalImage' ) ) {
      const tcEditing = editor.plugins.get( 'TrackChangesEditing' );

      tcEditing.enableCommand( 'toggleImageCaption', ( executeCommand, options ) => {
        executeCommand( options );
      }, { priority: 'high' } );
    }

    let tcOriginalValue;

    editor.model.document.on( 'change', () => {
      if ( !editor.disabledCommands ) {
        tcOriginalValue = trackChangesCommand.value;
      }
    }, { priority: 'highest' } );

    editor.model.document.on( 'change', () => {
      const range = editor.model.document.selection.getFirstRange();
      const ancestor = range.getCommonAncestor();

      if ( ancestor.name == 'caption' ) {
        commentCommand.forceDisabled( 'drupal-premium-features' );
        trackChangesCommand.forceDisabled( 'drupal-premium-features' );
        trackChangesCommand.value = false;

        editor.set( 'disabledCommands', true );
      } else {
        if ( editor.disabledCommands ) {
          commentCommand.clearForceDisabled( 'drupal-premium-features' );
          trackChangesCommand.clearForceDisabled( 'drupal-premium-features' );

          editor.set( 'disabledCommands', false );

          if ( tcOriginalValue ) {
            trackChangesCommand.value = true;
          }
        }
      }
    }, { priotity: 'low' } );
  }
}

export default DisableRealtimeMarkersInCaption;
