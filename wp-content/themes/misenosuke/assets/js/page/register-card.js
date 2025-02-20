$(document).ready(function(){
  // Stripeカード情報入力を初期化
  initCreditcard();
  
  // 利用規約を同意する
  $('#register-card-agreement').on('change',function() {
    if($(this).prop('checked')) {
      $('#register-card-submit').addClass('active');
      $(this).closest('.checkbox-check').removeClass('error');
    } else {
      $('#register-card-submit').removeClass('active');
    }
  });
  
  // 登録ボタンをクリックする
  $('#register-card-submit').on('click', function(){
    var form = $('#register-card-form');
    removeAllFormWarning(form);
    
    // Stripeでカードトークンを作成
    createToken(function(){
      // 新規登録フォーム送信
      var fd = new FormData(form[0]);
      fd.append('action', 'register_card');
      
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
          $('#register-card-warning-system').slideDown();
        }
      });
    });
  });
});