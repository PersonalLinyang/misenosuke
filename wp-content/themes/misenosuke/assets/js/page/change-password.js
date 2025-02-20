// 入力欄の内容が変わる
const password_input_change = function() {
  active_flag = true;
  $('.change-password-input').each(function(){
    if($(this).val() == '') {
      active_flag = false;
    }
  });
  if(active_flag) {
    $('#change-password-submit').addClass('active');
  } else {
    $('#change-password-submit').removeClass('active');
  }
}

$(document).ready(function(){
  $('.change-password-input').keyup(password_input_change);
  $('.change-password-input').change(password_input_change);
  
  // 登録ボタンをクリックする
  $('#change-password-submit').on('click', function(){
    var form = $('#change-password-form');
    removeAllFormWarning(form);
    
    var fd = new FormData();
    fd.append('action', 'pwdchange');
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
          // 成功メッセージを表示
          showPopupMessage(ucfirst(translations.pwdchange_success), 'success');
        } else {
          $.each(res['errors'], function(key, value) {
            addFormWarning(key, value);
          });
        }
      },
      error: function( response ){
        $('#change-password-warning-system').show();
      }
    });
  });
});