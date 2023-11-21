/*
 * Copyright (c) 2003-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

class AIAssistantAdapter {
  constructor( editor ) {
    this.editor = editor;
    if (this.editor.config._config.aiAssistant.proxyAuthKey) {
      this.useAuthKeyAsEndpoint();
    }
  }

  useAuthKeyAsEndpoint() {
    const tokenEndpoint = this.editor.config._config.aiAssistant.authKey;
    this.editor.config._config.aiAssistant.authKey = async () => {
      return await new Promise(async resolve => {
        const response = await fetch(tokenEndpoint);
        if (response.ok) {
          const token = response.text();
          resolve(token);
        }
        resolve()
      });
    };
  }

  static get pluginName() {
    return 'AIAssistantAdapter'
  }
}

export default AIAssistantAdapter;
