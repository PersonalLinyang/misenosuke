// 入力欄の内容が変わる
const login_input_change = function() {
  if($('#login-loginid').val() && $('#login-password').val()) {
    $('#login-submit').addClass('active');
  } else {
    $('#login-submit').removeClass('active');
  }
}

$(document).ready(function(){
  document.getElementById("login-loginid").addEventListener('change', login_input_change);
  document.getElementById("login-password").addEventListener('change', login_input_change);
  $('.login-input').keyup(login_input_change);
  
  // 登録ボタンをクリックする
  $('#login-submit').on('click', function(){
    var fd = new FormData();
    fd.append('action', 'login');
    $($('#login-form').serializeArray()).each(function(i, v) {
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
          window.location.href = res['url'];
        } else {
          $('#login-submit').closest('form').find('.warning').html('');
          $.each(res['errors'], function(key, value) {
            addFormWarning(key, value);
          });
        }
      },
      error: function( response ){
        $('#login-warning-system').slideDown();
      }
    });
  });
});