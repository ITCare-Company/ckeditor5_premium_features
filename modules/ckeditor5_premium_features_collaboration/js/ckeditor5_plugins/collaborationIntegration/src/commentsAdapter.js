import CollaborationStorage from "./collaborationStorage";

class CommentsAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);
  }

  static get pluginName() {
    return 'CommentsAdapter'
  }

  init() {
    const commentsRepositoryPlugin = this.editor.plugins.get( 'CommentsRepository' );
    const commentsRepositoryElement = document.querySelector(this.storage.getSourceDataSelector('comments'));
    // Load comments.
    const threads = JSON.parse(commentsRepositoryElement.value);
    for (const thread of threads) {
      commentsRepositoryPlugin.addCommentThread(thread);
    }

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

  updateStorage(plugin, storageElement) {
    storageElement.value = JSON.stringify(plugin.getCommentThreads({
      skipNotAttached: true,
      skipEmpty: true,
      toJSON: true
    }));
  }
}

export default CommentsAdapter;
