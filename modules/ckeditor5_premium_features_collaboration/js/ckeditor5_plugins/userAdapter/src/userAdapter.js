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
    const availablePermissions = {
      'admin': ['document:write', 'comment:admin'],
      'edit': ['document:write', 'comment:write'],
      'suggestions_only': ['document:write'],
      'comments_only': ['comment:write'],
      'ready_only': [],
    };
    let permissionsPlugin = this.editor.plugins.get('Permissions');
    let userEditorPermission = drupalSettings.ckeditor5Premium.current_user.editor_permission;
    let permissions = availablePermissions[userEditorPermission];
    if (typeof permissions === 'undefined' || permissions === null) {
      this.editor.enableReadOnlyMode(this.editor.id);
    } else {
      permissionsPlugin.setPermissions(permissions);

      if (userEditorPermission === 'suggestions_only' || userEditorPermission === 'comments_only') {
        this.disableToolbarItems();
        if (userEditorPermission === 'suggestions_only') {
          this.editor.execute('trackChanges');
        }
      }
    }
  }

  disableToolbarItems() {
    let toolbarItems = this.editor.ui.view.toolbar.items;
    toolbarItems.map(item => {
      if (item.label === 'Source') {
        item.set('isEnabled', false);
      }
      if (typeof item.buttonView !== "undefined") {
        if (item.buttonView.label === 'Track changes' ) {
          item.buttonView.actionView.set('isEnabled', false);
          item.buttonView.arrowView.set('isEnabled', false);
        }
        if (item.buttonView.label === 'Revision history') {
          item.set('isEnabled', false);
        }
      }
    });
  }

}

export default UserAdapter;
