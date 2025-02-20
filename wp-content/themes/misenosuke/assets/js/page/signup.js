$(document).ready(function(){
  // Stripeカード情報入力を初期化
  initCreditcard();
  
  // 利用規約を同意する
  $('#signup-agreement').on('change',function() {
    if($(this).prop('checked')) {
      $('#signup-submit').addClass('active');
      $(this).closest('.checkbox-check').removeClass('error');
    } else {
      $('#signup-submit').removeClass('active');
    }
  });
  
  // 登録ボタンをクリックする
  $('#signup-submit').on('click', function(){
    var form = $('#signup-form');
    removeAllFormWarning(form);
    
    // Stripeでカードトークンを作成
    createToken(function(){
      // 新規登録フォーム送信
      var fd = new FormData(form[0]);
      fd.append('action', 'signup');
      
      // ロゴファイルを追加
      var file = document.getElementById('signup-restaurantlogo').files[0];
      if (file) {
        fd.append('restaurant_logo', file);
      }
      
      $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: fd,
        processData: false,
        contentType: false,
        success: function( response ){
          var res = JSON.parse(response);
          if(res['result'] == true) {
            // 新規登録成功でページを遷移
            window.location.href = res['url'];
          } else {
            $.each(res['errors'], function(key, value) {
              addFormWarning(key, value);
            });
          }
        },
        error: function( response ){
          $('#signup-warning-system').slideDown();
        }
      });
    });
  });
});