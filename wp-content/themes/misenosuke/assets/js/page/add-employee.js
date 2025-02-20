$(document).ready(function(){
  // 登録ボタンをクリックする
  $('#add-employee-submit').on('click', function(){
    var form = $('#add-employee-form');
    removeAllFormWarning(form);
    
    // 新規登録フォーム送信
    var fd = new FormData(form[0]);
    fd.append('action', 'add_employee');
    
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
        $('#add-employee-warning-system').slideDown();
      }
    });
  });
});