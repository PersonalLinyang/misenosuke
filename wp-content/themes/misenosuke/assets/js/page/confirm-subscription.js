$(document).ready(function(){
  // サブスクリプション情報を確認する
  $('#confirm-subscription-agreement').on('change',function() {
    if($(this).prop('checked')) {
      $('#confirm-subscription-submit').addClass('active');
      $(this).closest('.checkbox-check').removeClass('error');
    } else {
      $('#confirm-subscription-submit').removeClass('active');
    }
  });
  
  // Submitボタンクリック
  $('#confirm-subscription-submit').click(function(){
    var form = $('#confirm-subscription-form');
    removeAllFormWarning(form);
    
    // スクリプション作成フォーム送信
    var fd = new FormData();
    fd.append('action', 'create_subscription');
    $(form.serializeArray()).each(function(i, v) {
      fd.append(v.name, v.value);
    });
    
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: fd,
      processData: false,
      contentType: false,
      success: function( response ){
        var res = JSON.parse(response);
        if(res['result'] == true) {
          // スクリプション成功でページを遷移
          window.location.href = res['url'];
        } else {
          $.each(res['errors'], function(key, value) {
            addFormWarning(key, value);
          });
        }
      },
      error: function( response ){
        $('#confirm-subscription-warning-system').slideDown();
      }
    });
  });
});