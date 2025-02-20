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
  
  form.find('.manage-editor-image-inner').css('background-image', '');
  form.find('.manage-editor-image-delete').removeClass('active');
  form.find('.manage-editor-option-empty').show();
  form.find('.manage-editor-option-item').remove();
  
  form.find('.manage-addoption').data('index', 0);
  
  form.find('.warning').hide();
  form.find('.error').removeClass('error');
  
  $('.manage-save').text(ucwords(translations.add)).removeClass('busy').addClass('active');
  $('.manage-editor-body').find('h3').text(ucwords(translations.add_course));
}


/*
 * コースリストに新しくコース要素を追加
 */
const addViewerCourse= function(name, slug, display_method="") {
  // index取得と更新
  var index = parseInt($('.manage-viewer-list').data('index'));
  $('.manage-viewer-list').data('index', index + 1);
  
  // HTML構築と追加
  var html = `
    <li class="manage-viewer-course" id="manage-viewer-course-` + slug + `" style="display: none" data-key="` + index + `">
      <p class="manage-viewer-controller controller-course controller-move manage-movecourse"></p>
      <p class="manage-viewer-course-name">` + name + `</p>
      <div class="manage-viewer-controller controller-course controller-control manage-control">
        <div class="manage-viewer-buttonlist">
          <p class="manage-viewer-button button-course button-edit manage-editcourse">` + ucwords(translations.edit_course) + `</p>
          <p class="manage-viewer-button button-course button-copy manage-copycourse">` + ucwords(translations.copy_course) + `</p>
          <p class="manage-viewer-button button-course button-delete manage-deletecourse">` + ucwords(translations.delete_course) + `</p>
        </div>
      </div>
      <input type="hidden" class="manage-viewer-course-priority" name="course_` + index + `_priority" value="" />
      <input type="hidden" class="manage-viewer-course-slug" name="course_` + index + `_slug" value="` + slug + `" />
    </li>
  `;
  $('.manage-viewer-list').append(html);
  
  // 新規追加するコース要素を取得
  var course = $('#manage-viewer-course-' + slug);
  
  // 表示順採番更新
  renumberCourse();
  
  // コース要素の動作を有効化
  initViewerCourse(course);
  
  // コース要素を表示
  if(display_method == 'slide') {
    course.slideDown();
  } else {
    course.show();
  }
}


/*
 * コース要素の動作を有効化
 */
const initViewerCourse = function(course) {
  var slug = course.find('.manage-viewer-course-slug').val();
  
  // 操作ボタンを有効化
  course.find('.manage-control').click(function(){
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
  course.find('.manage-editcourse').click(function(){
    showEditorCourse('edit', slug);
  });
  
  // コピーボタンを有効化
  course.find('.manage-copycourse').click(function(){
    showEditorCourse('copy', slug);
  });
  
  // 削除ボタンを有効化
  course.find('.manage-deletecourse').click(function(){
    showPopupDeletecourse($(this));
  });
}


/*
 * コースリスト順番採番更新
 */
const renumberCourse = function() {
  var counter = 0;
  $('.manage-viewer-course').each(function(){
    $(this).find('.manage-viewer-course-priority').val(counter);
    counter++;
  });
}


/*
 * コース編集エリアデータ入れ
 */
const showEditorCourse = async function(pattern, slug="") {
  var width_window = window.innerWidth;
  var form = $('#manage-course-form');
  
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
        url: ajaxurl + '?action=get_course&slug=' + slug,
        processData: false,
        contentType: false,
        success: function( response ){
          // 先に編集エリアをクリア
          clearEditor();
          
          var res = JSON.parse(response);
          if(res['result'] == true) {
            var data = res['course'];
            var option_list = form.find('.manage-editor-option-list');
            
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
            
            // 利用人数入力に値入れ
            form.find('input[name="min_people"]').val(data['min_people']);
            form.find('input[name="max_people"]').val(data['max_people']);
            
            // 利用可能時間帯選択に値入れ
            form.find('select[name="start_time"]').val(data['start_time']);
            form.find('select[name="end_time"]').val(data['end_time']);
            
            // 販売期間入力に値入れ
            form.find('input[name="start_date"]').val(data['start_date']);
            form.find('input[name="end_date"]').val(data['end_date']);
            
            // 時間制限入力に値入れ
            form.find('input[name="time_limit"]').val(data['time_limit']);
            
            // ラストオーダー入力に値入れ
            form.find('input[name="last_order"]').val(data['last_order']);
            
            form.find('.manage-editor-freemenus-select').val(data['free_menus']).trigger('change');
            
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
      form.find('.manage-editor-course-slug').val(slug);
      // 保存ボタンテキストを保存に変換
      $('.manage-save').text(ucwords(translations.save));
      // H3を編集に変換
      form.find('h3').text(ucwords(translations.edit_course));
    } else {
      // slug情報をクリアしてボタンを追加ボタンに切り替え
      form.find('.manage-editor-course-slug').val('');
      // 保存ボタンテキストを追加に変換
      $('.manage-save').text(ucwords(translations.add));
      if(pattern == 'copy') {
        // H3をコピーして作成に変換
        form.find('h3').text(ucwords(translations.copy_course));
        // コピー元に値入れ
        form.find('input[name="copy_from"]').val(slug);
      } else {
        // H3を新規作成して作成に変換
        form.find('h3').text(ucwords(translations.add_course));
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
 * コース削除ボタンを初期化
 */
const showPopupDeletecourse = function(button) {
  // 削除対象情報取得
  var course = button.closest('.manage-viewer-course');
  var slug = course.find('.manage-viewer-course-slug').val();
  var name = course.find('.manage-viewer-course-name').text();
  var popup = $('.manage-popup-delete');
  
  // 削除ポップアップのテキストとフォームを構築
  popup.find('.manage-popup-delete-name').text(name);
  popup.find('.manage-popup-delete-slug').val(slug);
  
  // 削除ポップアップを表示
  $('.popup-shadow').fadeIn();
  popup.fadeIn();
}


/*
 * コース保存
 */
const saveCourse = function() {
  var fd = new FormData();
  var form = $('#manage-course-form');
  fd.append('action', 'save_course');
  $(form.serializeArray()).each(function(i, v) {
    if (fd.has(v.name) && v.name == 'free_menus') {
      // 複数選択対応
      var existingValues = fd.get(v.name);
      if (!Array.isArray(existingValues)) {
        existingValues = [existingValues];
      }
      existingValues.push(v.value);
      fd.set(v.name, existingValues);
    } else {
      // 通常対応
      fd.append(v.name, v.value);
    }
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
        form.find('.manage-editor-course-slug').val(res['slug']);
        $('.manage-save').text(ucwords(translations.save));
        form.find('h3').text(ucwords(translations.edit_course));
        
        // 新規の場合リストに追加
        if($('#manage-viewer-course-' + res['slug']).length == 0) {
          addViewerCourse(res['course_name'], res['slug'], {}, 'slide');
        } else {
          $('#manage-viewer-course-' + res['slug']).find('.manage-viewer-course-name').text(res['course_name']);
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
        addFormWarning('system', ucfirst(translations.ajax_save_course_failed));
      });
      
      // 保存ボタンの処理中を外す
      $('.manage-save').removeClass('busy');
    }
  });
}


/*
 * メニューリストを初期化
 */
const initMenuList = function(menu_list) {
  // メニューのリストを取得
  $.ajax({
    type: 'GET',
    url: ajaxurl + '?action=get_all_menus',
    processData: false,
    contentType: false,
    success: function( response ){
      var res = JSON.parse(response);
      if(res['result'] == true) {
        $.each(res['list'], function(key, category){
          if(category['menus'].length > 0) {
            menu_list.append('<optgroup data-slug="' + category['slug'] + '" label="' + category['name'] + '"></optgroup>');
            menu_group = menu_list.find('optgroup[data-slug="' + category['slug'] + '"]');
            $.each(category['menus'], function(key, menu){
              menu_group.append('<option value="' + menu['slug'] + '">' + menu['name'] + '</option>')
            });
          }
        });
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
  
  // メニューの選択を初期化
  menu_list.select2({
    multiple: true, 
    width: '100%',
    placeholder: 'text',
    dropdownCssClass: "manage-editor-freemenus-drop",
    selectionCssClass: "manage-editor-freemenus-selection",
    dropdownParent: $('.manage-editor-freemenus'),
    matcher: function(params, data) {
        // params がオブジェクトである場合、直接入力されたテキストを取得する
        const inputText = $(".select2-search__field").val();

        // 入力文字列をスペースで分割して配列にする
        const terms = inputText.split(/[\s　]+/);
        let children = [];
        
        // optgroupを経由して子をループして、キーワードが全部あるものを取得
        if (data.children && data.children.length > 0) {
            for (let option of data.children) {
                let return_flag = true;
                const optionText = option.text.toLowerCase();
                for (let i = 0; i < terms.length; i++) {
                    if (optionText.indexOf(terms[i].toLowerCase()) === -1) {
                        return_flag = false;
                    }
                }
                if(return_flag) {
                  children.push(option);
                }
            }
        }
        
        // 元の選択肢に影響がないように選択肢リストをコピー
        let data_copy = $.extend(true, [], data);
        if(children.length > 0) {
          // 条件に満たす選択肢のみを返す
          data_copy['children'] = children;
          return data_copy;
        } else {
          return null;
        }
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
    url: ajaxurl + '?action=get_all_courses',
    processData: false,
    contentType: false,
    success: function( response ){
      var res = JSON.parse(response);
      if(res['result'] == true) {
        $.each(res['list'], function(key, course){
          addViewerCourse(course['name'], course['slug']);
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
  
  // コース追加ボタンをクリック
  $('.manage-addcourse').click(function(){
    showEditorCourse('add');
  });
  
  // 表示順保存ボタンをクリック
  $('.manage-savepriority').click(function(){
    if($(this).hasClass('busy')) {
      // 処理中の場合、処理を走らせない
      showPopupMessage(ucfirst(translations.saving_message), 'error');
    } else {
      // 処理中を追加し、重複処理を回避
      $(this).addClass('busy');
      
      renumberCourse();
      
      var fd = new FormData();
      var form = $('#manage-priority-form');
      fd.append('action', 'save_course_priority');
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
    handle: '.manage-movecourse',
    placeholder: 'manage-viewer-course-placeholder',
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
      renumberCourse();
    },
  });
  $('.manage-viewer-list').disableSelection();
  
  // クリアボタンをクリック
  $('.manage-clear').click(function() {
    $('.manage-editor-body').slideUp(function(){
      clearEditor();
      $('.manage-editor-body').slideDown();
    });
  });
  
  // カレンダーを有効化
  $(".manage-datepicker").datepicker({
    dateFormat: "yy-mm-dd",
    todayText: ucwords(translations.prev_month),
    prevText: ucwords(translations.prev_month),
    nextText: ucwords(translations.next_month),
    changeMonth: true,
    changeYear: true,
    showButtonPanel: true,
    beforeShow: function(input, inst) {
      inst.dpDiv.addClass('month_year_datepicker');
    },
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
      
      saveCourse();
    }
  });
  
  // コース一覧リストを初期化
  initMenuList($('.manage-editor-freemenus-select'));
  
  // コース削除確認フォーム削除ボタンをクリック
  $('.manage-deleteconfirm').click(function(){
    var fd = new FormData();
    var form = $('#manage-delete-form');
    fd.append('action', 'delete_course');
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
          var editor_slug = $('.manage-editor-course-slug').val();
          if(slug == editor_slug) {
            // 削除対象を編集中なら編集エリアをクリア
            clearEditor();
          }
          
          // 削除ポップアップを閉じる
          $('.popup-shadow').fadeOut();
          $('.popup-section').fadeOut();
          
          // メニューカテゴリリストから該当要素が消える
          var target = $('#manage-viewer-course-' + slug);
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