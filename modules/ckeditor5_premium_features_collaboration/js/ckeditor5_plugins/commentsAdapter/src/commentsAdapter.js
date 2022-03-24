// Application data will be available under a global variable `appData`.
const appData = {
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

  // Comment threads data.
  commentThreads: [
    {
      threadId: 'thread-1',
      comments: [
        {
          commentId: 'comment-1',
          authorId: 'user-1',
          content: '<p>Are we sure we want to use a made-up disorder name?</p>',
          createdAt: new Date( '09/20/2018 14:21:53' ),
          attributes: {}
        },
        {
          commentId: 'comment-2',
          authorId: 'user-2',
          content: '<p>Why not?</p>',
          createdAt: new Date( '09/21/2018 08:17:01' ),
          attributes: {}
        }
      ]
    }
  ],

};




class CommentsAdapter {
  constructor( editor ) {
    this.editor = editor;
  }

  static get pluginName() {
    return 'CommentsAdapter'
  }

  init() {
    const usersPlugin = this.editor.plugins.get( 'Users' );
    const commentsRepositoryPlugin = this.editor.plugins.get( 'CommentsRepository' );

    // Load the users data.
    for ( const user of appData.users ) {
      usersPlugin.addUser( user );
    }

    // Set the current user.
    usersPlugin.defineMe( appData.userId );

    // Set the adapter on the `CommentsRepository#adapter` property.
    commentsRepositoryPlugin.adapter = {
      addComment( data ) {
        console.log( 'Comment added', data );

        // Write a request to your database here. The returned `Promise`
        // should be resolved when the request has finished.
        // When the promise resolves with the comment data object, it
        // will update the editor comment using the provided data.
        return Promise.resolve( {
          createdAt: new Date()       // Should be set on the server side.
        } );
      },

      updateComment( data ) {
        console.log( 'Comment updated', data );

        // Write a request to your database here. The returned `Promise`
        // should be resolved when the request has finished.
        return Promise.resolve();
      },

      removeComment( data ) {
        console.log( 'Comment removed', data );

        // Write a request to your database here. The returned `Promise`
        // should be resolved when the request has finished.
        return Promise.resolve();
      },

      getCommentThread( data ) {
        console.log( 'Getting comment thread', data );

        // Write a request to your database here. The returned `Promise`
        // should resolve with the comment thread data.
        return Promise.resolve( {
          threadId: data.threadId,
          comments: [
            {
              commentId: 'comment-1',
              authorId: 'user-2',
              content: '<p>Are we sure we want to use a made-up disorder name?</p>',
              createdAt: new Date(),
              attributes: {}
            }
          ],
          isFromAdapter: true
        } );
      }
    };
  }
}



export default CommentsAdapter;
