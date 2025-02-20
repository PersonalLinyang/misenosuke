// 入力欄の内容が変わる
const pwdreset_input_change = function() {
  if($('#reset-password-loginid').val()) {
    $('#reset-password-submit').addClass('active');
  } else {
    $('#reset-password-submit').removeClass('active');
  }
}

$(document).ready(function(){
  document.getElementById("reset-password-loginid").addEventListener('change', pwdreset_input_change);
  $('#reset-password-loginid').keyup(pwdreset_input_change);
  
  // 登録ボタンをクリックする
  $('#reset-password-submit').on('click', function(){
    var fd = new FormData();
    fd.append('action', 'pwdreset');
    $($('#reset-password-form').serializeArray()).each(function(i, v) {
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
          $('#reset-password-form').slideUp(function(){
            $('#reset-password-complete').slideDown();
          });
        } else {
          $('#reset-password-form').find('.warning').slideUp(function(){
            $.each(res['errors'], function(key, value) {
              addFormWarning(key, value);
            });
          });
        }
      },
      error: function( response ){
        $('#reset-password-warning-system').show();
      }
    });
  });
});