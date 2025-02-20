$(document).ready(function(){
  // 保存ボタンをクリックする
  $('#edit-profile-submit').on('click', function(){
    var form = $('#edit-profile-form');
    removeAllFormWarning(form);
    
    var fd = new FormData();
    fd.append('action', 'edit_profile');
    $(form.serializeArray()).each(function(i, v) {
      fd.append(v.name, v.value);
    });
    
    // ロゴファイルを追加
    var file = document.getElementById('edit-profile-restaurantlogo').files[0];
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
          // 成功メッセージを表示
          showPopupMessage(ucfirst(translations.save_success), 'success');
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