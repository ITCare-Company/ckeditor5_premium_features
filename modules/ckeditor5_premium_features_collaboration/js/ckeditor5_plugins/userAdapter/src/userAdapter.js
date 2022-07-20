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
  }
}

export default UserAdapter;
