import CollaborationStorage from "../../collaborationStorage/src/collaborationStorage";

import Autoformat from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';
import List from '@ckeditor/ckeditor5-list/src/list';

class CommentsAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.storage = new CollaborationStorage(editor);

    this.editor.plugins._availablePlugins.set('Autoformat', Autoformat);
    this.editor.plugins._availablePlugins.set('Bold', Bold);
    this.editor.plugins._availablePlugins.set('Italic', Italic);
    this.editor.plugins._availablePlugins.set('List', List);

    const extraCommentsPlugins = Array.from(this.editor.plugins._availablePlugins.values()).filter(
        plugin => [ 'Bold', 'Italic', 'List', 'Autoformat' ].includes( plugin.pluginName ),
    );

    this.editor.config._config.comments.editorConfig.extraPlugins.push(...extraCommentsPlugins);
  }

  static get pluginName() {
    return 'CommentsAdapter'
  }

  static get requires() {
    return [ 'CommentsRepository' ];
  }

  init() {
    if (this.storage.processCollaborationCommandDisable("addCommentThread")) {
      return;
    }

    if (!this.editor.plugins.has('CommentsRepository')) {
      return
    }

    const commentsRepositoryPlugin = this.editor.plugins.get( 'CommentsRepository' );
    const commentsRepositoryElement = document.querySelector(this.storage.getSourceDataSelector('comments'));

    if (!commentsRepositoryElement || commentsRepositoryElement.value == '') {
      return;
    }

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
