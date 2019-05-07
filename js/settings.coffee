define [], ->
   getWindowVar = (key) ->
      if !window[key]
         throw "MISP_EXCEPTION: Invalid window var: " + key
      window[key]
   settings =
      width:   getWindowVar 'post_width'
      height:  getWindowVar 'post_height'
      id:      getWindowVar 'post_id'
      ajaxurl: getWindowVar 'ajaxurl'
      i18n:    getWindowVar 'mispI18n'
      nonce:   getWindowVar 'misp_nonce'
      options_nonce: getWindowVar 'misp_options_nonce'

