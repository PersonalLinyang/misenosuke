/*
 * 編集エリアクリア
 */
const clearEditor = function() {
  var form = $('.manage-editor-form');
  
  form.find('input[type="hidden"]').val('');
  form.find('input[type="text"]').val('');
  form.find('textarea').val('');
  form.find('select').val('');
  form.find('input[type="number"]').val('0');
  form.find('input[type="checkbox"]').prop('checked', false);
  form.find('input[type="radio"]').prop('checked', false);
  form.find('input[type="file"]').val('');
  
  form.find('.manage-editor-categoryselect').val('uncategorized');
  form.find('.manage-editor-image-inner').css('background-image', '');
  form.find('.manage-editor-image-delete').removeClass('active');
  form.find('.manage-editor-option-empty').show();
  form.find('.manage-editor-option-item').remove();
  
  form.find('.manage-addoption').data('index', 0);
  
  form.find('.warning').hide();
  form.find('.error').removeClass('error');
  
  $('.manage-save').text(ucwords(translations.add)).removeClass('busy').addClass('active');
  $('#manage-editor-category').find('h3').text(ucwords(translations.add_menu_category));
  $('#manage-editor-menu').find('h3').text(ucwords(translations.add_menu));
}


/*
 * カテゴリリストに新しくカテゴリ要素を追加
 */
const addViewerCategory = function(name, slug, menus, display_method="") {
  // index取得と更新
  var index = parseInt($('.manage-viewer-list').data('index'));
  $('.manage-viewer-list').data('index', index + 1);
  
  // 移動部分とボタン部分を構築
  var class_uncategorized = '';
  var html_move = '';
  var html_button = '';
  if(slug != 'uncategorized') {
    html_move = '<p class="manage-viewer-controller controller-category controller-move manage-movecategory"></p>';
    html_button = `
      <p class="manage-viewer-button button-category button-edit manage-editcategory">` + ucwords(translations.edit_menu_category) + `</p>
      <p class="manage-viewer-button button-category button-copy manage-copycategory">` + ucwords(translations.copy_menu_category) + `</p>
      <p class="manage-viewer-button button-category button-delete manage-deletecategory">` + ucwords(translations.delete_menu_category) + `</p>
    `;
  } else {
    class_uncategorized = 'uncategorized';
  }
  
  // HTML構築と追加
  var html = `
    <li class="manage-viewer-category ` + class_uncategorized + `" id="manage-viewer-category-` + slug + `" style="display: none" data-key="` + index + `">
      <div class="manage-viewer-category-header">
        ` + html_move + `
        <p class="manage-viewer-category-name">` + name + `</p>
        <div class="manage-viewer-controller controller-category controller-control manage-control">
          <div class="manage-viewer-buttonlist">
            ` + html_button + `
            <p class="manage-viewer-button button-category button-add manage-addmenu">` + ucwords(translations.add_menu) + `</p>
          </div>
        </div>
        <p class="manage-viewer-controller controller-category controller-show manage-showmenu"></p>
      </div>
      <div class="manage-viewer-category-body">
        <ul class="manage-viewer-menu-list" data-index="0"></ul>
        <p class="manage-viewer-category-empty">` + ucfirst(translations.category_without_menu) + `</p>
      </div>
      <input type="hidden" class="manage-viewer-category-priority" name="category_` + index + `_priority" value="" />
      <input type="hidden" class="manage-viewer-category-slug" name="category_` + index + `_slug" value="` + slug + `" />
    </li>
  `;
  if($('#manage-viewer-category-uncategorized').length == 0) {
    $('.manage-viewer-list').append(html);
  } else {
    $('#manage-viewer-category-uncategorized').before(html);
  }
  
  // 新規追加するカテゴリ要素を取得
  var category = $('#manage-viewer-category-' + slug);
  
  // 表示順採番更新
  renumberCategory();
  
  // カテゴリ要素の動作を有効化
  initViewerCategory(category);
  
  // カテゴリ要素を表示
  if(display_method == 'slide') {
    category.slideDown();
  } else {
    category.show();
  }
}


/*
 * カテゴリ要素の動作を有効化
 */
const initViewerCategory = function(category) {
  var header = category.find('.manage-viewer-category-header');
  var slug = category.find('.manage-viewer-category-slug').val();
  
  // メニュー並び替えを有効化
  category.find('.manage-viewer-menu-list').sortable({
    handle: '.manage-movemenu',
    placeholder: 'manage-viewer-menu-placeholder',
    start: function(event, ui) {
      ui.item.addClass('dragging');
    },
    stop: function(event, ui) {
      ui.item.removeClass('dragging');
      renumberMenu(ui.item.closest('.manage-viewer-menu-list'));
    },
  });
  
  // 操作ボタンを有効化
  header.find('.manage-control').click(function(){
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
  header.find('.manage-editcategory').click(function(){
    showEditorCategory('edit', slug);
  });
  
  // コピーボタンを有効化
  header.find('.manage-copycategory').click(function(){
    showEditorCategory('copy', slug);
  });
  
  // 削除ボタンを有効化
  header.find('.manage-deletecategory').click(function(){
    showPopupDeletecategory($(this));
  });
  
  // メニュー追加ボタンを有効化
  header.find('.manage-addmenu').click(function(){
    showEditorMenu('add', slug);
  });
  
  // メニューリスト折り畳みボタンを有効化
  header.find('.manage-showmenu').click(function() {
    $(this).toggleClass('active');
    category.find('.manage-viewer-category-body').slideToggle();
  });
}


/*
 * カテゴリリスト順番採番更新
 */
const renumberCategory = function() {
  var counter = 0;
  var select = $('.manage-editor-categoryselect');
  $('.manage-viewer-category').each(function(){
    $(this).find('.manage-viewer-category-priority').val(counter);
    var slug = $(this).find('.manage-viewer-category-slug').val();
    if(slug != 'uncategorized') {
      var option = select.find('option[value="' + slug + '"]');
      if(option.length == 0) {
        select.append('<option value="' + slug + '">' + $(this).find('.manage-viewer-category-name').text() + '</option>');
      } else {
        option.appendTo(select);
      }
    }
    counter++;
  });
}


/*
 * メニューカテゴリ編集エリアデータ入れ
 */
const showEditorCategory = async function(pattern, slug="") {
  var width_window = window.innerWidth;
  var form = $('#manage-category-form');
  
  // メニュー編集エリアを非表示
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
        url: ajaxurl + '?action=get_menu_category&slug=' + slug,
        processData: false,
        contentType: false,
        success: function( response ){
          // 先に編集エリアをクリア
          clearEditor();
          
          var res = JSON.parse(response);
          if(res['result'] == true) {
            var data = res['category'];
            var option_list = form.find('.manage-editor-option-list');
            
            // 名前入力に値入れ
            $.each(data['names'], function(language, text) {
              form.find('input[name="name_' + language + '"]').val(text);
            });
            
            // 説明入力に値入れ
            $.each(data['descriptions'], function(language, text) {
              form.find('textarea[name="description_' + language + '"]').val(text);
            });
            
            if(data['options'].length) {
              $.each(data['options'], function(index, option) {
                // オプションリストに新要素のHTMLを追加
                var html = htmlOption(index, option);
                option_list.append(html);
                
                // 新規追加したオプション要素を初期化
                var item = option_list.find('.option-item-' + index);
                initOption(item);
              });
              
              // オプションの順番を再採番
              renumberOption(option_list);
              
              // オプション空白提示を非表示
              option_list.find('.manage-editor-option-empty').hide();
              option_list.find('.manage-editor-option-item').show();
            }
            
            // オプションの数をオプション追加ボタンのindexに更新
            form.find('.manage-addoption').data('index', data['options'].length);
            
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
      form.find('.manage-editor-category-slug').val(slug);
      $('.manage-save').text(ucwords(translations.save));
      form.find('h3').text(ucwords(translations.edit_menu_category));
    } else {
      // slug情報をクリアしてボタンを追加ボタンに切り替え
      form.find('.manage-editor-category-slug').val('');
      $('.manage-save').text(ucwords(translations.add));
      if(pattern == 'copy') {
        form.find('h3').text(ucwords(translations.copy_menu_category));
      } else {
        form.find('h3').text(ucwords(translations.add_menu_category));
      }
    }
    
    // カテゴリ編集エリアを表示
    $('.manage-save').data('target', 'category');
    if(width_window > 768) {
      $('#manage-editor-category').slideDown(800);
    } else {
      $('#manage-editor-category').show();
    }
    
    // [SPサイト]編集エリアを表示
    $('.manage-editor-shadow').fadeIn(800);
    $('.manage-editor').animate({'bottom': 0}, 800, 'linear', function(){});
  });
}


/*
 * カテゴリ削除ボタンを初期化
 */
const showPopupDeletecategory = function(button) {
  // 削除対象情報取得
  var category = button.closest('.manage-viewer-category');
  var slug = category.find('.manage-viewer-category-slug').val();
  var name = category.find('.manage-viewer-category-name').text();
  var popup = $('.manage-popup-deletecategory');
  
  // 削除ポップアップのテキストとフォームを構築
  popup.find('.manage-popup-delete-name').text(name);
  popup.find('.manage-popup-delete-slug').val(slug);
  
  // 削除ポップアップを表示
  $('.popup-shadow').fadeIn();
  popup.fadeIn();
}


/*
 * メニューカテゴリ保存
 */
const saveCategory = function() {
  var fd = new FormData();
  var form = $('#manage-category-form');
  fd.append('action', 'save_menu_category');
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
        form.find('.manage-editor-category-slug').val(res['slug']);
        $('.manage-save').text(ucwords(translations.save));
        form.find('h3').text(ucwords(translations.edit_menu_category));
        
        // 新規の場合リストに追加
        if($('#manage-viewer-category-' + res['slug']).length == 0) {
          addViewerCategory(res['category_name'], res['slug'], {}, 'slide');
        } else {
          $('#manage-viewer-category-' + res['slug']).find('.manage-viewer-category-name').text(res['category_name']);
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
        addFormWarning('system', ucfirst(translations.ajax_save_category_failed));
      });
      
      // 保存ボタンの処理中を外す
      $('.manage-save').removeClass('busy');
    }
  });
}


/*
 * メニューリストに新しく追加
 */
const addViewerMenu = function(name, slug, category_slug, display_method="") {
  var category = $('#manage-viewer-category-' + category_slug);
  var cat_key = category.data('key');
  
  // index取得と更新
  var list = category.find('.manage-viewer-menu-list');
  var index = parseInt(list.data('index'));
  list.data('index', index + 1);
  
  // HTML構築と追加
  var html = `
    <li class="manage-viewer-menu" id="manage-viewer-menu-` + slug + `" style="display: none">
      <p class="manage-viewer-controller controller-menu controller-move manage-movemenu"></p>
      <p class="manage-viewer-menu-name">` + name + `</p>
      <div class="manage-viewer-controller controller-menu controller-control manage-control">
        <div class="manage-viewer-buttonlist">
          <p class="manage-viewer-button button-menu button-edit manage-editmenu">` + ucwords(translations.edit_menu) + `</p>
          <p class="manage-viewer-button button-menu button-copy manage-copymenu">` + ucwords(translations.copy_menu) + `</p>
          <p class="manage-viewer-button button-menu button-delete manage-deletemenu">` + ucwords(translations.delete_menu) + `</p>
        </div>
      </div>
      <input type="hidden" class="manage-viewer-menu-priority" name="menu_` + cat_key + `_` + index + `_priority" value="">
      <input type="hidden" class="manage-viewer-menu-slug" name="menu_` + cat_key + `_` + index + `_slug" value="` + slug + `">
    </li>
  `;
  list.append(html);
  
  // 新規追加するメニュー要素を取得
  var menu = $('#manage-viewer-menu-' + slug);
  
  // 表示順採番更新
  renumberMenu(list);
  
  // メニュー要素の動作を有効化
  initViewerMenu(menu);
  
  // メニュー要素を表示
  if(display_method == 'slide') {
    category.find('.manage-viewer-category-empty').slideUp();
    menu.slideDown();
  } else {
    category.find('.manage-viewer-category-empty').hide();
    menu.show();
  }
}


/*
 * メニュー要素の動作を有効化
 */
const initViewerMenu = function(menu) {
  var slug = menu.find('.manage-viewer-menu-slug').val();
  
  // 操作ボタンを有効化
  menu.find('.manage-control').click(function(){
    // 任意箇所クリック時操作リストを閉じる処理を無効化
    event.stopPropagation();
    
    // ボタンリストの表示を切り替え
    var buttonlist = $(this).find('.manage-viewer-buttonlist');
    var hide_process = $('.manage-viewer-buttonlist').not(buttonlist).slideUp(300);
    $.when(hide_process).done(function(){
      buttonlist.slideToggle(300);
    });
  });
  
  // 編集ボタンを有効化
  menu.find('.manage-editmenu').click(function(){
    showEditorMenu('edit', slug);
  });
  
  // コピーボタンを有効化
  menu.find('.manage-copymenu').click(function(){
    showEditorMenu('copy', slug);
  });
  
  // 削除ボタンを有効化
  menu.find('.manage-deletemenu').click(function(){
    showPopupDeletemenu($(this));
  });
}


/*
 * メニューリスト順番採番更新
 */
const renumberMenu = function(list) {
  var counter = 0;
  list.find('.manage-viewer-menu').each(function(){
    $(this).find('.manage-viewer-menu-priority').val(counter);
    counter++;
  });
}


/*
 * メニュー編集エリアデータ入れ
 * slugは編集/コピー時は対象メニューのスラッグ、追加時は対象カテゴリのスラッグ
 */
const showEditorMenu = function(pattern, slug="") {
  var width_window = window.innerWidth;
  var form = $('#manage-menu-form');
  
  // メニュー編集エリアを非表示
  if(width_window > 768) {
    var hide_process = $('.manage-editor-body').slideUp(800);
  } else {
    var hide_process = $('.manage-editor-body').hide();
  }
  
  // 保存ボタンを一時的無効化
  $('.manage-save').removeClass('active');
  
  $.when(hide_process).done(async function(){
    if(pattern == 'edit' || pattern == 'copy') {
      // AJAXで対象メニュー情報取得
      await $.ajax({
        type: 'GET',
        url: ajaxurl + '?action=get_menu&slug=' + slug,
        processData: false,
        contentType: false,
        success: function( response ){
          // 先に編集エリアをクリア
          clearEditor();
          
          var res = JSON.parse(response);
          if(res['result'] == true) {
            var data = res['menu'];
            var option_list = form.find('.manage-editor-option-list');
            
            // カテゴリ選択に値入れ
            $('.manage-editor-categoryselect').val(data['category']);
            
            // 名前入力に値入れ
            $.each(data['names'], function(language, text) {
              form.find('input[name="name_' + language + '"]').val(text);
            });
            
            // 説明入力に値入れ
            $.each(data['descriptions'], function(language, text) {
              form.find('textarea[name="description_' + language + '"]').val(text);
            });
            
            // 値段入力に値入れ
            form.find('input[name="price"]').val(data['price']);
            
            // 画像アップロードに背景入れ
            if(data['image']) {
              form.find('.manage-editor-image-inner').css('background-image', 'url(' + data['image'] + ')');
              form.find('.manage-editor-image-delete').addClass('active');
            }
            
            // タグリストにチェック入れ
            $.each(data['tags'], function(index, value) {
              form.find('input[name="tag[]"][value="' + value + '"]').prop('checked', true);
            });
            
            if(data['options'].length) {
              $.each(data['options'], function(index, option) {
                // オプションリストに新要素のHTMLを追加
                var html = htmlOption(index, option);
                option_list.append(html);
                
                // 新規追加したオプション要素を初期化
                var item = option_list.find('.option-item-' + index);
                initOption(item);
              });
              
              // オプションの順番を再採番
              renumberOption(option_list);
              
              // オプション空白提示を非表示
              option_list.find('.manage-editor-option-empty').hide();
              option_list.find('.manage-editor-option-item').show();
            }
            
            // オプションの数をオプション追加ボタンのindexに更新
            form.find('.manage-addoption').data('index', data['options'].length);
            
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
      // slug情報を入れる
      form.find('.manage-editor-menu-slug').val(slug);
      // 保存ボタンテキストを保存に変換
      $('.manage-save').text(ucwords(translations.save));
      // H3を編集に変換
      form.find('h3').text(ucwords(translations.edit_menu));
    } else {
      // slug情報をクリア
      form.find('.manage-editor-menu-slug').val('');
      // 保存ボタンテキストを追加に変換
      $('.manage-save').text(ucwords(translations.add));
      
      if(pattern == 'copy') {
        // H3をコピーして作成に変換
        form.find('h3').text(ucwords(translations.copy_menu));
        // コピー元に値入れ
        form.find('input[name="copy_from"]').val(slug);
      } else {
        // H3を新規作成して作成に変換
        form.find('h3').text(ucwords(translations.add_menu));
        // カテゴリ選択に値入れ
        $('.manage-editor-categoryselect').val(slug);
      }
    }
    
    // メニュー編集エリアを表示
    $('.manage-save').data('target', 'menu');
    if(width_window > 768) {
      $('#manage-editor-menu').slideDown(800);
    } else {
      $('#manage-editor-menu').show();
    }
    
    // [SPサイト]編集エリアを表示
    $('.manage-editor-shadow').fadeIn(800);
    $('.manage-editor').animate({'bottom': 0}, 800, 'linear', function(){});
  });
}


/*
 * メニュー削除ボタンを初期化
 */
const showPopupDeletemenu = function(button) {
  // 削除対象情報取得
  var menu = button.closest('.manage-viewer-menu');
  var slug = menu.find('.manage-viewer-menu-slug').val();
  var name = menu.find('.manage-viewer-menu-name').text();
  var popup = $('.manage-popup-deletemenu');
  
  // 削除ポップアップのテキストとフォームを構築
  popup.find('.manage-popup-delete-name').text(name);
  popup.find('.manage-popup-delete-slug').val(slug);
  
  // 削除ポップアップを表示
  $('.popup-shadow').fadeIn();
  popup.fadeIn();
}


/*
 * メニュー保存
 */
const saveMenu = function() {
  var fd = new FormData();
  var form = $('#manage-menu-form');
  fd.append('action', 'save_menu');
  $(form.serializeArray()).each(function(i, v) {
    fd.append(v.name, v.value);
  });
  
  // 画像ファイルを追加
  var file = document.getElementById('manage-editor-image-file').files[0];
  if (file) {
    fd.append('image', file);
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
        // 編集エリアを編集モードに変更
        form.find('.manage-editor-menu-slug').val(res['slug']);
        $('.manage-save').text(ucwords(translations.save));
        form.find('h3').text(ucwords(translations.edit_menu));
        
        if($('#manage-viewer-menu-' + res['slug']).length == 0) {
          // 新規の場合リストに追加
          addViewerMenu(res['menu_name'], res['slug'], res['category'], 'slide');
        } else {
          var current_category = $('#manage-viewer-menu-' + res['slug']).closest('.manage-viewer-category').find('.manage-viewer-category-slug').val();
          if(current_category != res['category']) {
            // カテゴリ変化がある場合ビューリストを更新
            $('#manage-viewer-menu-' + res['slug']).slideUp(function(){
              $('#manage-viewer-menu-' + res['slug']).remove();
              addViewerMenu(res['menu_name'], res['slug'], res['category'], 'slide');
            });
          } else {
            $('#manage-viewer-menu-' + res['slug']).find('.manage-viewer-menu-name').text(res['menu_name']);
          }
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
        addFormWarning('system', ucfirst(translations.ajax_save_menu_failed));
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
  // メニューカテゴリの設定ボタン以外のところをクリックするとボタンリストを非表示
  $(document).click(function() {
    $('.manage-viewer-buttonlist').slideUp(300);
  });
  
  // メニュー一覧リストを初期化
  $.ajax({
    type: 'GET',
    url: ajaxurl + '?action=get_all_menus',
    processData: false,
    contentType: false,
    success: function( response ){
      var res = JSON.parse(response);
      if(res['result'] == true) {
        $.each(res['list'], function(key, category){
          addViewerCategory(category['name'], category['slug']);
          $.each(category['menus'], function(key, menu){
            addViewerMenu(menu['name'], menu['slug'], category['slug']);
          });
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
  
  // カテゴリ追加ボタンをクリック
  $('.manage-addcategory').click(function(){
    showEditorCategory('add');
  });
  
  // 表示順保存ボタンをクリック
  $('.manage-savepriority').click(function(){
    if($(this).hasClass('busy')) {
      // 処理中の場合、処理を走らせない
      showPopupMessage(ucfirst(translations.saving_message), 'error');
    } else {
      // 処理中を追加し、重複処理を回避
      $(this).addClass('busy');
      
      renumberCategory();
      $('.manage-viewer-menu-list').each(function(){
        renumberMenu($(this));
      });
      
      var fd = new FormData();
      var form = $('#manage-priority-form');
      fd.append('action', 'save_menu_priority');
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
            showPopupMessage(ucfirst(translations.ajax_save_success), 'success');
          } else {
            // エラーメッセージを表示
            showPopupMessage(res['error'], 'error');
          }
          $(this).removeClass('busy');
        },
        error: function( response ){
          // エラーメッセージを表示
          showPopupMessage(ucfirst(translations.ajax_save_priority_failed), 'error');
          $(this).removeClass('busy');
        }
      });
    }
  });
  
  // カテゴリ並び替えを有効化
  $('.manage-viewer-list').sortable({
    handle: '.manage-movecategory',
    placeholder: 'manage-viewer-category-placeholder',
    cancel: '.uncategorized',
    start: function(event, ui) {
      ui.item.addClass('dragging');
    },
    change: function(event, ui) {
      var index = $('.uncategorized').index();
      if(ui.placeholder.index() >= index) {
        ui.placeholder.insertBefore($('.uncategorized'));
      }
    },
    stop: function(event, ui) {
      ui.item.removeClass('dragging');
      renumberCategory();
    },
  });
  $('.manage-viewer-list').disableSelection();
  
  // クリアボタンをクリック
  $('.manage-clear').click(function() {
    $('.manage-editor-body').slideUp(function(){
      clearEditor();
      $('#manage-editor-' + $('.manage-save').data('target')).slideDown();
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
      
      // 処理対象によって保存関数を呼び出し
      var target = $(this).data('target');
      if(target == 'category') {
        saveCategory();
      } else if(target == 'menu') {
        saveMenu();
      } else {
        showPopupMessage(ucfirst(translations.ajax_save_nothing), 'error');
        $(this).removeClass('busy');
      }
    }
  });
  
  // カテゴリ削除確認フォーム削除ボタンをクリック
  $('#manage-deletecategoryconfirm').click(function(){
    var fd = new FormData();
    var form = $('#manage-deletecategory-form');
    fd.append('action', 'delete_menu_category');
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
          var editor_slug = $('.manage-editor-category-slug').val();
          if(slug == editor_slug) {
            // 削除対象を編集中なら編集エリアをクリア
            clearEditor();
          }
          
          // 削除ポップアップを閉じる
          $('.popup-shadow').fadeOut();
          $('.popup-section').fadeOut();
          
          // メニューカテゴリリストから該当要素が消える
          var target = $('#manage-viewer-category-' + slug);
          target.slideUp(function(){
            target.find('.manage-viewer-menu').each(function(){
              $(this).prop('id', '');
              addViewerMenu($(this).find('.manage-viewer-menu-name').text(), $(this).find('.manage-viewer-menu-slug').val(), 'uncategorized', 'slide');
            });
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
  
  // メニュー削除確認フォーム削除ボタンをクリック
  $('#manage-deletemenuconfirm').click(function(){
    var fd = new FormData();
    var form = $('#manage-deletemenu-form');
    fd.append('action', 'delete_menu');
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
          var editor_slug = $('.manage-editor-menu-slug').val();
          if(slug == editor_slug) {
            // 削除対象を編集中なら編集エリアをクリア
            clearEditor();
          }
          
          // 削除ポップアップを閉じる
          $('.popup-shadow').fadeOut();
          $('.popup-section').fadeOut();
          
          // メニューカテゴリリストから該当要素が消える
          var target = $('#manage-viewer-menu-' + slug);
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