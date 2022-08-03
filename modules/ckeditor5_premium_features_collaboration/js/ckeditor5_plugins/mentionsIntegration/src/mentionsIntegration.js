class MentionsIntegration {
  constructor( editor ) {
    this.editor = editor;

    if (typeof this.editor.plugins._availablePlugins == 'undefined' ||
      !this.editor.plugins._availablePlugins.has('Mention') ||
      typeof drupalSettings.ckeditor5Premium.mentions == "undefined") {
      return;
    }

    const mentionConfig = {
      feeds: [
        {
          feed: this.getFeedItems,
          marker: drupalSettings.ckeditor5Premium.mentions.marker,
          minimumCharacters: drupalSettings.ckeditor5Premium.mentions.minCharacter,
          dropdownLimit: drupalSettings.ckeditor5Premium.mentions.dropdownLimit
        }
      ],
    }
    this.editor.config._config.mention = mentionConfig;

    if (typeof this.editor.config._config.comments != "undefined") {
      this.editor.config._config.comments.editorConfig.extraPlugins.push(this.editor.plugins._availablePlugins.get('Mention'));
      this.editor.config._config.comments.editorConfig.mention = mentionConfig
    }
  }

  static get pluginName() {
    return 'MentionsIntegration'
  }

  /**
   * Query API endpoint to collect matching users.
   *
   * @param queryText
   *   Username phrase.
   *
   * @returns {Promise<unknown>}
   */
  getFeedItems(queryText) {
    if (typeof drupalSettings.ckeditor5Premium.mentions == "undefined") {
      return;
    }

    return new Promise( resolve => {
      jQuery.ajax('/ck5/api/annotations', {
        data: {
          query: queryText,
        },
        success: function(result) {
          resolve( result );
        }
      });
    } );
  }
}

export default MentionsIntegration;
