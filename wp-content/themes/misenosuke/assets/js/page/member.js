/*
 * 従業員削除ボタンを初期化
 */
const initMemberEmployeeDelete = function(button) {
  // 削除対象情報取得
  var tr = button.closest('tr');
  var uid = button.data('uid');
  var name = tr.find('.member-employee-name').text();
  
  // 削除ポップアップのテキストとフォームを構築
  $('.member-employee-delete-name').text(name);
  $('.member-employee-delete-uid').val(uid);
  
  // 削除ポップアップを表示
  $('.popup-shadow').fadeIn();
  $('.member-employee-delete').fadeIn();
}


$(document).ready(function(){
  // 請求明細ボタンをクリックする
  $('.member-invoice-button-detail').click(function(){
    var row = $(this).data('row');
    $('#invoice-detail-' + row).slideToggle();
  });
  
  // 料理係/接客係追加ボタンをクリック
  $('.member-employee-button-add').click(function(){
    var restaurant = $(this).data('uid');
    var type = $(this).data('type');
    var url = lang_domain + '/add-employee/?restaurant=' + restaurant + '&type=' + type; // 指定のURL
    $('#member-employee-addqr-qr').empty(); // QRコードを生成する前にクリア
    new QRCode(document.getElementById("member-employee-addqr-qr"), {
        text: url,
        width: 128,
        height: 128
    });
    // 従業員追加ポップアップを表示
    $('.popup-shadow').fadeIn();
    $('.member-employee-addqr').fadeIn();
  });
  
  // 従業員削除ボタンをクリック
  $('.member-employee-delete-button').click(function(){
    initMemberEmployeeDelete($(this));
  });
  
  // 従業員削除確認フォーム閉じるボタンをクリック
  $('.member-employee-delete-close').click(function(){
    $('.popup-shadow').fadeOut();
    $('.popup-section').fadeOut();
  });
  
  // カテゴリ削除確認フォーム削除ボタンをクリック
  $('.member-employee-delete-submit').click(function(){
    var fd = new FormData();
    var form = $('#member-employee-delete-form');
    fd.append('action', 'delete_employee');
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
          showPopupMessage(ucfirst(translations.ajax_delete_employee_success), 'success');
          $('.popup-shadow').fadeOut();
          $('.popup-section').fadeOut();
        } else {
          // エラーメッセージを表示
          showPopupMessage(res['error'], 'error');
        }
      },
      error: function( response ){
        // エラーメッセージを表示
        showPopupMessage(ucfirst(translations.ajax_delete_employee_failed), 'error');
      }
    });
  });
  
  // メニューカテゴリの展開ボタンをクリック
  $('.member-menu-category-controller').click(function(){
    if($(this).hasClass('active')) {
      $(this).closest('.member-menu-category-item').find('.member-menu-category-body').slideDown();
      $(this).removeClass('active');
    } else {
      $(this).closest('.member-menu-category-item').find('.member-menu-category-body').slideUp();
      $(this).addClass('active');
    }
  });
});