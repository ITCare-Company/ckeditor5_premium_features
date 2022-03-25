class TrackChangesAdapter {
  constructor( editor ) {
    this.editor = editor;
    this.config = this.editor.config;
    this.basePath = '/ckeditor5/premium/collaboration/suggestion';
  }

  static get pluginName() {
    return 'TrackChangesAdapter'
  }

  init() {
    const trackChangesPlugin = this.editor.plugins.get( 'TrackChanges' );

    /**
     * Call the session endpoint in order to get the CSRF token.
     *
     * @returns {Promise<string>}
     */
    function fetchToken() {
      return fetch('/session/token').then(response => response.text())
    }

    trackChangesPlugin.adapter = {

      /**
       * Called each time the suggestion data is needed.
       *
       * The method should return a promise that resolves with the suggestion data object.
       *
       * @param {String} id The ID of a suggestion to get.
       * @returns {Promise}
       */
      getSuggestion: id => {
        return fetch( this.basePath + '/' + id )
          .then( response => response.json() )
          .then( suggestion => {
            suggestion.createdAt = new Date( suggestion.created * 1000 );
            suggestion.authorId = suggestion.user;
            suggestion.hasComments = !!parseInt( suggestion.has_comments );

            return suggestion;
          } );
      },

      /**
       * Called each time a new suggestion is created.
       *
       * The method should save the suggestion data in the database
       * and return a promise that will be resolved when the save is
       * completed. If the promise resolves with a suggestion data object,
       * the suggestion in the editor will be updated using the data from the server.
       *
       * The `data` object does not expect the `authorId` property.
       * For security reasons, the author of the suggestions should be set
       * on the server side.
       *
       * If `params.originalSuggestionId` is set, the new suggestion should
       * have `authorId` property set to the same as the suggestion with
       * `originalSuggestionId`. This happens when one user breaks
       * another user's suggestion, creating a new suggestion in a result.
       *
       * In any other case, use current (local) user to set `authorId`.
       *
       * The `data` object does not expect the `createdAt` property either.
       * You should use the server-side time generator to ensure that all users
       * see the same date.
       *
       * @param {Object} params
       * @param {String} params.id The suggestion ID.
       * @param {String} [params.originalSuggestionId] Id of a suggestion from which
       * `authorId` property should be taken.
       * @param {Object|null} [params.data] Additional suggestion data.
       * @returns {Promise}
       */
      addSuggestion: params => {
        return fetchToken().then((csrf_token) => {
          const formData = new FormData();
          formData.append( 'id', params.id );
          formData.append( 'entity_type', this.config.get('routeContext.type'));
          formData.append( 'entity_id', this.editor.config.get('routeContext.id'));
          formData.append( 'data', JSON.stringify( params.data ) );

          if ( params.originalSuggestionId ) {
            formData.append( 'original', params.originalSuggestionId );
          }

          return fetch( this.basePath, {
            method: 'POST',
            body: formData,
            headers: new Headers({
              'X-CSRF-Token': csrf_token,
            })
          } )
            .then( response => response.json() )
            .then( responseData => {
              return {
                createdAt: new Date( responseData.created * 1000 )
              };
            } );
        })
      },

      /**
       * Called each time the suggestion data has changed. The only data that
       * may change is information whether the suggestion has comments or not.
       * So, if the first comment is added to the suggestion or the only
       * comment is removed from the suggestion, the adapter method
       * is called with proper data.
       *
       * For the suggestions with a falsy `hasComments` flag, the editor
       * will not try to fetch the comment thread through the comments adapter.
       *
       * The method should update the suggestion data in the database
       * and return a promise that should be resolved when the save is
       * completed.
       *
       * @param {String} id The suggestion ID.
       * @param {Object} options
       * @param {Boolean} options.hasComments Information if
       * the suggestion has comments or not.
       * @returns {Promise}
       */
      updateSuggestion: ( id, options ) => {
        return fetchToken().then((csrf_token) => {
          const formData = new FormData();

          if (options.hasComments !== undefined) {
            formData.append('has_comments', options.hasComments);
          }

          return fetch('/suggestions/update/' + id, {
            method: 'PUT',
            headers: new Headers({
              'X-CSRF-Token': csrf_token,
            })
          });
        });
      }
    };
  }
}

export default TrackChangesAdapter;
