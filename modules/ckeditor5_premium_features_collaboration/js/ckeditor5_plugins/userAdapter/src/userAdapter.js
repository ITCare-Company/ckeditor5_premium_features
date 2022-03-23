// Application data will be available under a global variable `appData`.
const userData = {
  // Users data.
  users: [
    {
      id: 'user-1',
      name: 'Joe Doe',
      // Note that the avatar is optional.
      avatar: 'https://randomuser.me/api/portraits/thumb/men/26.jpg'
    },
    {
      id: 'user-2',
      name: 'Ella Harper',
      avatar: 'https://randomuser.me/api/portraits/thumb/women/65.jpg'
    }
  ],

  // The ID of the current user.
  userId: 'user-1',

};

class UserAdapter {
  constructor( editor ) {
    this.editor = editor;
  }

  static get pluginName() {
    return 'UserAdapter'
  }

  init() {
    const usersPlugin = this.editor.plugins.get( 'Users' );

    // Load the users data.
    for ( const user of userData.users ) {
      usersPlugin.addUser( user );
    }

    // Set the current user.
    usersPlugin.defineMe( userData.userId );
  }
}

export default UserAdapter;
