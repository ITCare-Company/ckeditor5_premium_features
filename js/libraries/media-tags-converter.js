(function ($, Drupal) {
  Drupal.CKEditor5PremiumFeatures.mediaTagsConverter = {
    convertMediaTags(content) {
      console.log('media tag lib');

      let documentDom = document.createElement('body');
      documentDom.innerHTML = content;

      let elementsAttributes = this.getTagProperties(documentDom);

      let mediaPaths = this.queryMediaPaths(elementsAttributes);

      console.log(elementsAttributes);
      console.log(mediaPaths);

      return 'converted: ' + documentDom.innerHTML;
    },

    getTagProperties(documentDom) {
      let elements = documentDom.getElementsByTagName("drupal-media");
      let elementsAttributes = [];

      for (let e = 0; e < elements.length; ++e) {
        let type = elements[e].dataset.entityType;
        let id = elements[e].dataset.entityUuid;

        elementsAttributes.push({type: type, id: id});
        let props = elements[e].attributes;
        console.log(props);
      }

      return elementsAttributes;
    },

    async queryMediaPaths(elementAttributes) {
      // return new Promise( resolve => {
      let res = [];
        await $.post('/ck5/api/media-tags', {
          media: JSON.stringify(elementAttributes)
        }).done(function(result) {
          console.log( result );
          res = result;
          return result;
        });

        console.log(res);

        return res;
      // } );
    },

    /**
     *
     * @param documentElement
     * @param mediaPaths
     */
    replaceMediaTags(documentElements, mediaPaths) {
      for (let i = 0; i < documentElements.length; ++i) {
        let element = documentElements[i];
      }
    }
  }
})(jQuery, Drupal);
