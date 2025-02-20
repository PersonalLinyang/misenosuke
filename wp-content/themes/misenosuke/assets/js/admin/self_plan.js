jQuery.noConflict();
(function($) {
  $(document).ready(function(){
    listeningCreatedElement = function(class_name, function_name) {
      // Mutation Observerを作成します。
      const observer = new MutationObserver(function(mutations) {
          mutations.forEach(function(mutation) {
              // 変更されたノードに対してループします。
              mutation.addedNodes.forEach(function(node) {
                  // 追加されたノードが要素の場合
                  if (node.nodeType === Node.ELEMENT_NODE) {
                      // 要素にqtranxs-lang-switch-wrapクラスがあるかどうかチェックします。
                      if (node.classList.contains(class_name)) {
                          function_name();
                      }
                  }
              });
          });
      });

      // 監視するターゲットを選択します。ここではbody要素を監視します。
      const targetNode = document.body;

      // Mutation Observerを設定します。
      observer.observe(targetNode, {
          childList: true,
          subtree: true
      });

      // jQueryのイベントが終わったらobserverを切断するようにする
      $(window).on('unload', function() {
          observer.disconnect();
      });
    }
    
    listeningCreatedElement('qtranxs-lang-switch-wrap', function(){
      $('.qtranxs-lang-switch-wrap').remove();
    });
  });
})(jQuery);