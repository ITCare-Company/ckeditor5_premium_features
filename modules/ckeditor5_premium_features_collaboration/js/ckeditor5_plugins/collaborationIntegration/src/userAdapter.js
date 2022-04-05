class UserAdapter {
  constructor( editor ) {
    this.editor = editor;
  }

  static get pluginName() {
    return 'UserAdapter'
  }

  init() {
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
