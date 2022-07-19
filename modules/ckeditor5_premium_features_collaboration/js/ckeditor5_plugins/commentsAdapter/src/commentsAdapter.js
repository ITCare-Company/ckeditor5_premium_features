import CollaborationStorage from "../../collaborationStorage/src/collaborationStorage";

class CommentsAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
  }

  static get pluginName() {
    return 'CommentsAdapter'
  }

  static get requires() {
    return [ 'CommentsRepository', 'UserAdapter' ]
  }

  init() {
    const commentsRepositoryPlugin = this.editor.plugins.get( 'CommentsRepository' );
    const commentsRepositoryElement = document.querySelector(this.storage.getSourceDataSelector('comments'));

    if (!commentsRepositoryElement) {
      return;
    }

    // Load comments.
    const threads = JSON.parse(commentsRepositoryElement.value);

    console.log(commentsRepositoryPlugin);

    for (let thread of threads) {
      console.log(thread.threadId);
      console.log(commentsRepositoryPlugin.hasCommentThread(thread.threadId));
      commentsRepositoryPlugin.addCommentThread(thread);
    }

    console.log('koniec');

    // Observe data change and update the data fields.
    this.editor.model.document.on( 'comments:change:data', () => {
      this.updateStorage(commentsRepositoryPlugin, commentsRepositoryElement);
    });

    this.editor.model.document.on( 'trackchanges:change:data', () => {
      this.updateStorage(commentsRepositoryPlugin, commentsRepositoryElement);
    });

    const events = [
      'addComment',
      'change',
      'removeComment',
      'removeCommentThread',
      'updateComment',
    ];

    for (const event of events) {
      commentsRepositoryPlugin.on(event, () => {
        this.editor.model.document.fire('comments:change:data');
      });
    }
  }

  destroy() {
    // this.updateStorage(this.commentsRepositoryPlugin, this.commentsRepositoryElement);
    console.log('destroy');

    // After the editor is initialized, add an action to be performed after a button is clicked.
    const commentsRepository = this.editor.plugins.get( 'CommentsRepository' );

    const editorData = this.editor.data.get();
    const commentThreadsData = commentsRepository.getCommentThreads( {
      skipNotAttached: true,
      skipEmpty: true,
      toJSON: true
    } );

    console.log(editorData);
    console.log(commentThreadsData);
  }

  updateStorage(plugin, storageElement) {
    storageElement.value = JSON.stringify(plugin.getCommentThreads({
      skipNotAttached: true,
      skipEmpty: true,
      toJSON: true
    }));
  }
}

export default CommentsAdapter;
