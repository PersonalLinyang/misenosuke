/*
 * 編集エリアクリア
 */
const clearEditor = function() {
  var form = $('.manage-editor-form');
  
  form.find('input[type="hidden"]').val('');
  form.find('input[type="text"]').val('');
  form.find('input[type="number"]').val('0');
  
  form.find('.warning').hide();
  form.find('.error').removeClass('error');
  
  $('.manage-save').text(ucwords(translations.add)).removeClass('busy').addClass('active');
  $('.manage-editor-body').find('h3').text(ucwords(translations.add_seat));
}


/*
 * 席リストに新しく席要素を追加
 */
const addViewerSeat= function(name, slug, display_method="") {
  // index取得と更新
  var index = parseInt($('.manage-viewer-list').data('index'));
  $('.manage-viewer-list').data('index', index + 1);
  
  // HTML構築と追加
  var html = `
    <li class="manage-viewer-seat" id="manage-viewer-seat-` + slug + `" style="display: none" data-key="` + index + `">
      <p class="manage-viewer-seat-name">` + name + `</p>
      <div class="manage-viewer-controller controller-seat controller-control manage-control">
        <div class="manage-viewer-buttonlist">
          <p class="manage-viewer-button button-seat button-edit manage-editseat">` + ucwords(translations.edit_seat) + `</p>
          <p class="manage-viewer-button button-seat button-copy manage-copyseat">` + ucwords(translations.copy_seat) + `</p>
          <p class="manage-viewer-button button-seat button-delete manage-deleteseat">` + ucwords(translations.delete_seat) + `</p>
        </div>
      </div>
      <input type="hidden" class="manage-viewer-seat-slug" name="seat_` + index + `_slug" value="` + slug + `" />
    </li>
  `;
  $('.manage-viewer-list').append(html);
  
  // 新規追加するコース要素を取得
  var seat = $('#manage-viewer-seat-' + slug);
  
  // 席要素の動作を有効化
  initViewerSeat(seat);
  
  // 席要素を表示
  if(display_method == 'slide') {
    seat.slideDown();
  } else {
    seat.show();
  }
}


/*
 * 席要素の動作を有効化
 */
const initViewerSeat = function(seat) {
  var slug = seat.find('.manage-viewer-seat-slug').val();
  
  // 操作ボタンを有効化
  seat.find('.manage-control').click(function(){
    // 任意箇所クリック時操作リストを閉じる処理を無効化
    event.stopPropagation();
    
    // ボタンリストの表示を切り替え
    var buttonlist = $(this).find('.manage-viewer-buttonlist');
    var hide_process = $('.manage-viewer-buttonlist').not(buttonlist).slideUp();
    $.when(hide_process).done(function(){
      buttonlist.slideToggle();
    });
  });
  
  // 編集ボタンを有効化
  seat.find('.manage-editseat').click(function(){
    showEditorSeat('edit', slug);
  });
  
  // コピーボタンを有効化
  seat.find('.manage-copyseat').click(function(){
    showEditorSeat('copy', slug);
  });
  
  // 削除ボタンを有効化
  seat.find('.manage-deleteseat').click(function(){
    showPopupDeleteseat($(this));
  });
}


/*
 * 席編集エリアデータ入れ
 */
const showEditorSeat = async function(pattern, slug="") {
  var width_window = window.innerWidth;
  var form = $('#manage-seat-form');
  
  // コース編集エリアを非表示
  if(width_window > 768) {
    var hide_process = $('.manage-editor-body').slideUp(800);
  } else {
    var hide_process = $('.manage-editor-body').hide();
  }
  
  // 保存ボタンを一時的無効化
  $('.manage-save').removeClass('active');
  
  $.when(hide_process).done(async function(){
    if(pattern == 'edit' || pattern == 'copy') {
      // AJAXで対象カテゴリ情報取得
      await $.ajax({
        type: 'GET',
        url: ajaxurl + '?action=get_seat&slug=' + slug,
        processData: false,
        contentType: false,
        success: function( response ){
          // 先に編集エリアをクリア
          clearEditor();
          
          var res = JSON.parse(response);
          if(res['result'] == true) {
            var data = res['seat'];
            
            // 名前入力に値入れ
            form.find('input[name="name"]').val(data['name']);
            
            // 人数入力に値入れ
            form.find('input[name="people"]').val(data['people']);
            
            // 保存ボタンを有効化
            $('.manage-save').addClass('active');
          } else {
            // エラーメッセージを表示
            showPopupMessage(res['error'], 'error');
          }
        },
        error: function( response ){
          // エラーメッセージを表示
          showPopupMessage(ucfirst(translations.ajax_get_failed), 'error');
        }
      });
    } else {
      // 編集エリアをクリア
      clearEditor();
      
      // 保存ボタンを有効化
      $('.manage-save').addClass('active');
    }
    
    if(pattern == 'edit') {
      // slug情報を入れてボタンを保存ボタンに切り替え
      form.find('.manage-editor-seat-slug').val(slug);
      // 保存ボタンテキストを保存に変換
      $('.manage-save').text(ucwords(translations.save));
      // H3を編集に変換
      form.find('h3').text(ucwords(translations.edit_seat));
    } else {
      // slug情報をクリアしてボタンを追加ボタンに切り替え
      form.find('.manage-editor-seat-slug').val('');
      // 保存ボタンテキストを追加に変換
      $('.manage-save').text(ucwords(translations.add));
      if(pattern == 'copy') {
        // H3をコピーして作成に変換
        form.find('h3').text(ucwords(translations.copy_seat));
        // コピー元に値入れ
        form.find('input[name="copy_from"]').val(slug);
      } else {
        // H3を新規作成して作成に変換
        form.find('h3').text(ucwords(translations.add_seat));
      }
    }
    
    // コース編集エリアを表示
    if(width_window > 768) {
      $('.manage-editor-body').slideDown(800);
    } else {
      $('.manage-editor-body').show();
    }
    
    // [SPサイト]編集エリアを表示
    $('.manage-editor-shadow').fadeIn(800);
    $('.manage-editor').animate({'bottom': 0}, 800, 'linear', function(){});
  });
}


/*
 * 席削除ボタンを初期化
 */
const showPopupDeleteseat = function(button) {
  // 削除対象情報取得
  var seat = button.closest('.manage-viewer-seat');
  var slug = seat.find('.manage-viewer-seat-slug').val();
  var name = seat.find('.manage-viewer-seat-name').text();
  var popup = $('.manage-popup-delete');
  
  // 削除ポップアップのテキストとフォームを構築
  popup.find('.manage-popup-delete-name').text(name);
  popup.find('.manage-popup-delete-slug').val(slug);
  
  // 削除ポップアップを表示
  $('.popup-shadow').fadeIn();
  popup.fadeIn();
}


/*
 * 席保存
 */
const saveSeat = function() {
  var fd = new FormData();
  var form = $('#manage-seat-form');
  fd.append('action', 'save_seat');
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
        // 編集エリアを編集モードに変更
        form.find('.manage-editor-seat-slug').val(res['slug']);
        $('.manage-save').text(ucwords(translations.save));
        form.find('h3').text(ucwords(translations.edit_seat));
        
        // 新規の場合リストに追加
        if($('#manage-viewer-seat-' + res['slug']).length == 0) {
          addViewerSeat(res['seat_name'], res['slug'], {}, 'slide');
        } else {
          $('#manage-viewer-seat-' + res['slug']).find('.manage-viewer-seat-name').text(res['seat_name']);
        }
        
        // 成功メッセージを表示
        showPopupMessage(ucfirst(translations.ajax_save_success), 'success');
      } else {
        // エラーメッセージを表示
        var p1 = form.find('.warning').slideUp();
        var p2 = form.find('.error').removeClass('error');
        $.when(p1,p2).done(function(){
          $.each(res['errors'], function(key, value) {
            addFormWarning(key, value);
          });
        });
      }
      
      // 保存ボタンの処理中を外す
      $('.manage-save').removeClass('busy');
    },
    error: function( response ){
      // エラーメッセージを表示
      var p1 = form.find('.warning').slideUp();
      var p2 = form.find('.error').removeClass('error');
      $.when(p1,p2).done(function(){
        addFormWarning('system', ucfirst(translations.ajax_save_seat_failed));
      });
      
      // 保存ボタンの処理中を外す
      $('.manage-save').removeClass('busy');
    }
  });
}


/*
 * JS処理開始
 */
$(document).ready(function(){
  // コースの設定ボタン以外のところをクリックするとボタンリストを非表示
  $(document).click(function() {
    $('.manage-viewer-buttonlist').slideUp(300);
  });
  
  // コース一覧リストを初期化
  $.ajax({
    type: 'GET',
    url: ajaxurl + '?action=get_all_seats',
    processData: false,
    contentType: false,
    success: function( response ){
      var res = JSON.parse(response);
      if(res['result'] == true) {
        $.each(res['list'], function(key, seat){
          addViewerSeat(seat['name'], seat['slug']);
        });
        $('.manage-viewer-loading').slideUp('fast');
        $('.manage-viewer-body').slideDown(500);
        showPopupMessage(ucfirst(translations.ajax_get_list_success), 'success');
      } else {
        // エラーメッセージを表示
        showPopupMessage(res['error'], 'error');
      }
    },
    error: function( response ){
      // エラーメッセージを表示
      showPopupMessage(ucfirst(translations.ajax_get_list_failed), 'error');
    }
  });
  
  // 席追加ボタンをクリック
  $('.manage-addseat').click(function(){
    showEditorSeat('add');
  });
  
  // クリアボタンをクリック
  $('.manage-clear').click(function() {
    $('.manage-editor-body').slideUp(function(){
      clearEditor();
      $('.manage-editor-body').slideDown();
    });
  });
  
  // 保存ボタンをクリック
  $('.manage-save').click(function() {
    if(!$(this).hasClass('active')) {
      // 有効ではない場合、処理を走らせない
      showPopupMessage(ucfirst(translations.save_unactive), 'error');
    } else if($(this).hasClass('busy')) {
      // 処理中の場合、処理を走らせない
      showPopupMessage(ucfirst(translations.saving_message), 'error');
    } else {
      // 処理中を追加し、重複処理を回避
      $(this).addClass('busy');
      
      saveSeat();
    }
  });
  
  // 席削除確認フォーム削除ボタンをクリック
  $('.manage-deleteconfirm').click(function(){
    var fd = new FormData();
    var form = $('#manage-delete-form');
    fd.append('action', 'delete_seat');
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
          var slug = res['slug'];
          var editor_slug = $('.manage-editor-seat-slug').val();
          if(slug == editor_slug) {
            // 削除対象を編集中なら編集エリアをクリア
            clearEditor();
          }
          
          // 削除ポップアップを閉じる
          $('.popup-shadow').fadeOut();
          $('.popup-section').fadeOut();
          
          // メニューカテゴリリストから該当要素が消える
          var target = $('#manage-viewer-seat-' + slug);
          target.slideUp(function(){
            target.remove();
          });
          
          // 成功メッセージを表示
          showPopupMessage(ucfirst(translations.ajax_delete_success), 'success');
        } else {
          // エラーメッセージを表示
          showPopupMessage(res['error'], 'error');
        }
      },
      error: function( response ){
        // エラーメッセージを表示
        showPopupMessage(ucfirst(translations.ajax_delete_failed), 'error');
      }
    });
  });
});