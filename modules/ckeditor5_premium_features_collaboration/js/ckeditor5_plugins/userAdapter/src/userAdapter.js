class UserAdapter {
  constructor( editor ) {
    this.editor = editor;
  }

  static get pluginName() {
    return 'UserAdapter'
  }

  init() {
    if (typeof drupalSettings.ckeditor5Premium === "undefined" || !this.editor.plugins.has('Users') ) {
      return;
    }

    const usersPlugin = this.editor.plugins.get( 'Users' );
    const users = drupalSettings.ckeditor5Premium.users;

    for (const user in users) {
      usersPlugin.addUser(users[user]);
    }

    // Set the current user.
    usersPlugin.defineMe( drupalSettings.user.uid );

    this.editor.on('ready', () => {
      this.setPermissions();
    })
  }

  setPermissions() {
    let textFormat = this.editor.sourceElement.getAttribute('data-editor-active-text-format');
    let permissionsPlugin = this.editor.plugins.get('Permissions');
    let permissions = drupalSettings.ckeditor5Premium.current_user.editor_permission[textFormat];
    let documentAdmin = permissions.indexOf('document:admin');
    const isTrackChangesEnabled = this.editor.plugins.has('TrackChanges');

    if (documentAdmin > -1) {
      permissions.splice(documentAdmin, 1);
    }

    if (typeof permissions !== 'object') {
      this.editor.enableReadOnlyMode(this.editor.id);
    } else {
      permissionsPlugin.setPermissions(permissions);
      if (permissions.length === 1 && permissions[0] === 'comment:write') {
        this.disableToolbarItems();
      }
      if (permissions.includes('document:write') && documentAdmin === -1) {
        if (isTrackChangesEnabled) {
          this.editor.execute('trackChanges');
          this.editor.commands.get('acceptSuggestion').forceDisabled('suggestionOnly');
          this.editor.commands.get('acceptAllSuggestions').forceDisabled('suggestionOnly');
          this.editor.commands.get('discardAllSuggestions').forceDisabled('suggestionOnly');
          this.editor.commands.get('discardSuggestion').forceDisabled('suggestionOnly');
          this.disableToolbarItems();
        } else {
          this.editor.enableReadOnlyMode(this.editor.id);
        }
      }

    }
  }

  disableToolbarItems() {
    let toolbarItems = this.editor.ui.view.toolbar.items;
    toolbarItems.map(item => {
      if (item.label === 'Source') {
        item.set('isEnabled', false);
        item.set('isVisible', false);
      }
      if (typeof item.buttonView !== "undefined") {
        if (item.buttonView.label === 'Track changes' ) {
          item.buttonView.actionView.set('isEnabled', false);
          item.buttonView.arrowView.set('isEnabled', false);
          item.buttonView.arrowView.set('isVisible', false);
        }
        if (item.buttonView.label === 'Revision history') {
          item.set('isEnabled', false);
        }
      }
    });
  }

}

export default UserAdapter;
