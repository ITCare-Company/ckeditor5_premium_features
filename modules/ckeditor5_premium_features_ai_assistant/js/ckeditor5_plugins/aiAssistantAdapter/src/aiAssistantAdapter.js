/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

class AiAssistantAdapter{

  static get pluginName() {
    return 'AiAssistantAdapter'
  }
  constructor( editor ) {
    this.editor = editor;
    // console.log("editor: ", editor);
    // console.log("this: ", this);
    // this.AIAssistantUI = this.editor.plugins.get('AIAssistantUI');
    // console.log("UI: ", this.AIAssistantUI);
  }

  init() {
  }

  afterInit() {
    // let balloon = this.editor.plugins.get('ContextualBalloon');
    // console.log("balloon: ", balloon);
    // this.AIAssistantUI.listenTo(balloon, 'set', (eventInfo, name, value, oldValue) => {
    //   if (name != "visibleView") {
    //     return;
    //   }
    //   console.log("eventInfo: ", eventInfo);
    //   console.log("name: ", name);
    //   console.log("value: ", value);
    //   console.log("oldValue: ", oldValue);
    //     // const parentLink = this.AIAssistantUI._getSelectedLinkElement();
    //     // console.log(parentLink);
    //     // if (parentLink) {
    //     //   // Then show panel but keep focus inside editor editable.
    //     //   this.AIAssistantUI._showUI();
    //     // }
    // });
  }
}

export default AiAssistantAdapter;
