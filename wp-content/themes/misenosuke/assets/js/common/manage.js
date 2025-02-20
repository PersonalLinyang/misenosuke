/*
 * オプションの一要素のHTML文を構築
 */
const htmlOption = function(index, option = {}) {
  var html_option_name = '';
  var html_option_warning = '';
  var html_option_choices = '';
  $('.manage-language-tabhandler').each(function(){
    // 編集可能な言語をループ
    var language = $(this).data('language');
    
    // 現在編集中の言語以外入力欄を非表示
    var hidden_class = 'hidden';
    if($(this).hasClass('active')) {
      hidden_class = '';
    }
    
    // オプション項目名現在値を取得
    var name = '';
    if('names' in option) {
      if(language in option['names']) {
        name = option['names'][language];
      }
    }
    
    // 項目名入力欄とエラー提示欄HTML構築
    html_option_name += `
      <div class="manage-language-tab tab-` + language + ` ` + hidden_class + `">
        <input type="text" name="option_` + index + `_name_` + language + `" value="` + name + `" />
      </div>
    `;
    html_option_warning += '<p class="warning warning-option_' + index + '_name_' + language + '"></p>';
  });
  
  var choices_number = 0;
  if('choices' in option) {
    // オプションの既存選択肢数を取得
    choices_number = option['choices'].length;
    
    if(choices_number) {
      // 選択肢編集部分HTMLを構築
      $.each(option['choices'], function(choice_index, choice) {
        html_option_choices += htmlChoice(index, choice_index, choice);
      });
    }
  }
  
  // オプション全体のHTMLを構築
  html = `
    <li class="manage-editor-option-item option-item-` + index + `" data-index="` + index + `">
      <input type="hidden" class="manage-viewer-optionpriority" name="option_` + index + `_priority" value="" />
      <p class="manage-editor-option-button manage-moveoption"></p>
      <p class="manage-editor-option-button manage-deleteoption">×</p>
      <p class="manage-editor-option-button manage-showchoice"></p>
      <p class="manage-editor-option-button manage-hidechoice"></p>
      <div class="form-line">
        <p class="form-title">` + ucwords(translations.item) + `</p>
        <div class="form-input">
          ` + html_option_name + `
          ` + html_option_warning + `
          <p class="warning warning-option_` + index + `_name"></p>
        </div>
      </div>
      <div class="manage-editor-choice">
        <div class="manage-editor-line">
          <p class="form-title">` + ucwords(translations.choices) + `</p>
          <p class="manage-editor-button manage-addchoice" data-index="` + choices_number + `">` + ucwords(translations.add_choice) + `</p>
        </div>
        <ul class="form-input manage-editor-choice-list">
          <p class="warning center warning-option_` + index + `_choices"></p>
          <p class="form-input manage-editor-choice-empty">` + ucfirst(translations.no_choice) + `</p>
          ` + html_option_choices + `
        </ul>
      </div>
    </li>
  `;
  
  return html;
}


/*
 * オプションを初期化
 */
const initOption =function(option) {
  // 選択肢隠しボタンをクリック
  option.find('.manage-hidechoice').click(function(){
    option.find('.manage-editor-choice').slideUp(function(){
      option.find('.manage-hidechoice').hide();
      option.find('.manage-showchoice').show();
    });
  });
  
  // 選択肢表示ボタンをクリック
  option.find('.manage-showchoice').click(function(){
    option.find('.manage-editor-choice').slideDown(function(){
      option.find('.manage-hidechoice').show();
      option.find('.manage-showchoice').hide();
    });
  });
  
  // 選択肢追加ボタンをクリック
  option.find('.manage-addchoice').click(function(){
    var option_index = option.data('index');
    var choice_index = $(this).data('index');
    
    // 選択肢要素を追加
    var html = htmlChoice(option_index, choice_index);
    option.find('.manage-editor-choice-list').append(html);
    
    // 新選択肢を初期化
    var item = option.find('.choice-item-' + option_index + '-' + choice_index);
    initChoice(option, item);
    
    // 選択肢の順番を再採番
    renumberChoice(option);
    
    // 選択肢空白提示を非表示
    option.find('.manage-editor-choice-empty').slideUp(function(){
      // 新選択肢を表示
      item.slideDown();
    });
    
    // 選択肢追加ボタンindexを更新
    $(this).data('index', parseInt(choice_index) + 1);
  });
  
  // オプション削除ボタンをクリック
  option.find('.manage-deleteoption').click(function(){
    option.slideUp(function(){
      option.remove();
      renumberOption(option.closest('.manage-editor-option-list'));
      if($('.manage-editor-option-item').length == 0) {
        $('.manage-editor-option-empty').slideDown();
      }
    });
  });
  
  // 選択肢並び替えを有効化
  option.find('.manage-editor-choice-list').sortable({
    handle: '.manage-movechoice',
    placeholder: 'manage-editor-choice-placeholder',
    start: function(event, ui) {
      ui.item.addClass('dragging');
    },
    stop: function(event, ui) {
      ui.item.removeClass('dragging');
      renumberChoice(option);
    },
  });
  
  // 既存選択肢要素がある場合
  if(option.find('.manage-editor-choice-item').length) {
    // 既存選択肢要素を有効化
    option.find('.manage-editor-choice-item').each(function() {
      initChoice(option, $(this));
    });
    
    // 既存選択肢順番を再採番
    renumberChoice(option);
    
    // 選択肢空白提示を非表示して全選択肢を表示
    option.find('.manage-editor-choice-empty').hide();
    option.find('.manage-editor-choice-item').show();
  }
}


/*
 * オプションに変化ある（削除か並び替え）場合中身のinput要素のnameに再採番
 */
const renumberOption = function(list) {
  var counter = 0;
  list.find('.manage-editor-option-item').each(function(){
    $(this).find('.manage-viewer-optionpriority').val(counter);
    counter++;
  });
}


/*
 * オプションの選択肢の一要素のHTML文を構築
 */
const htmlChoice = function(option_index, choice_index, choice = {}) {
  var html_choice_name = '';
  var html_choice_warning = '';
  $('.manage-language-tabhandler').each(function(){
    // 編集可能言語をループ
    var language = $(this).data('language');
    
    // 現在の言語のみ入力欄を表示
    var hidden_class = 'hidden';
    if($(this).hasClass('active')) {
      hidden_class = '';
    }
    
    // 現在の選択肢名前を表示
    var name = '';
    if('names' in choice) {
      if(language in choice['names']) {
        name = choice['names'][language];
      }
    }
    
    // 選択肢名前とエラー提示HTMLを構築
    html_choice_name += `
      <div class="line-content manage-language-tab manage-editor-choice-name tab-` + language + ` ` + hidden_class + `">
        <input type="text" name="option_` + option_index + `_choice_` + choice_index + `_name_` + language + `" value="` + name + `" />
      </div>
    `;
    html_choice_warning += '<p class="warning warning-option_' + option_index + '_choice_' + choice_index + '_name_' + language + '"></p>';
  });
  
  // 現在の選択肢価格を表示
  var price = 0;
  if('price' in choice) {
    price = choice['price'];
  }
  
  // 選択肢全体のHTMLを構築
  html = `
    <li class="form-input manage-editor-choice-item choice-item-` + option_index + `-` + choice_index + `">
      <input type="hidden" class="manage-viewer-choicepriority" name="option_` + option_index + `_choice_` + choice_index + `_priority" value="" />
      <div class="manage-editor-choice-line">
        <p class="manage-editor-choice-button manage-movechoice"></p>
        ` + html_choice_name + `
        <div class="manage-editor-price">
          <input type="number" class="manage-editor-choice-pricenumber" name="option_` + option_index + `_choice_` + choice_index + `_price" value="` + price + `" />
        </div>
        <p class="manage-editor-choice-button manage-deletechoice">-</p>
      </div>
      ` + html_choice_warning + `
      <p class="warning warning-option_` + option_index + `_choice_` + choice_index + `_price"></p>
      <p class="warning warning-option_` + option_index + `_choice_` + choice_index + `"></p>
    </li>
  `;
  
  return html;
}


/*
 * オプションの選択肢を初期化
 */
const initChoice = function(option, choice) {
  // 選択肢削除ボタンをクリック
  choice.find('.manage-deletechoice').click(function(){
    choice.slideUp(function(){
      choice.remove();
      renumberChoice(option);
      if(option.find('.manage-editor-choice-item').length == 0) {
        option.find('.manage-editor-choice-empty').slideDown();
      }
    });
  });
}


/*
 * オプションの選択肢に変化ある（削除か並び替え）場合中身のinput要素のnameに再採番
 */
const renumberChoice = function(option) {
  var counter = 0;
  option.find('.manage-editor-choice-item').each(function(){
    $(this).find('.manage-viewer-choicepriority').val(counter);
    counter++;
  });
}


/*
 * JS処理開始
 */
$(document).ready(function(){
  // 編集言語切り替え
  $('.manage-language-tabhandler').click(function(){
    var language = $(this).data('language');
    $('.manage-language-tabhandler').removeClass('active');
    $(this).addClass('active');
    $('.manage-language-tab').addClass('hidden');
    $('.manage-language-tab.tab-' + language).removeClass('hidden');
  });
  
  // 画像をアップロード
  $('#manage-editor-image-file').change(function(e){
    var reader = new FileReader();
    reader.onload = function(e) {
      // 画像部分背景変更
      $('.manage-editor-image-inner').css('background-image', 'url(' + e.target.result + ')');
      // 画像削除ボタンを初期化
      $('.manage-editor-image-delete').addClass('active');
      $('input[name="image_delete"]').prop('checked', false);
      // コピー元情報を消す
      $('input[name="copy_from"]').val('');
    }
    reader.readAsDataURL(e.target.files[0]);
  });
  
  // 画像削除ボタンをクリック
  $('.manage-editor-image-delete').click(function(){
    // 画像アップロードを初期化
    $('#manage-editor-image-file').val('');
    // 画像部分背景を消す
    $('.manage-editor-image-inner').css('background-image', '');
    // 画像削除ボタンを非表示
    $(this).removeClass('active');
    // コピー元情報を消す
    $('input[name="copy_from"]').val('');
  });
  
  // [SPサイト]編集エリアシャドーをクリック
  $('.manage-editor-shadow').click(function(){
    // 編集エリアを非表示
    var header_height = $('.header').height();
    $('.manage-editor-shadow').fadeOut(800);
    $('.manage-editor').animate({'bottom': '-100vh'}, 800, 'linear', function(){});
  });
  
  // 共通オプション追加ボタンをクリック
  $('.manage-addoption').click(function(){
    var index = $(this).data('index');
    var list = $(this).closest('.manage-editor-content').find('.manage-editor-option-list');
    
    // 新オプションHTMLを追加
    var html = htmlOption(index);
    list.append(html);
    
    // 新オプションを初期化
    var item = list.find('.option-item-' + index);
    initOption(item);
    
    // オプションの順番を再採番
    renumberOption(list);
    
    // オプション空白提示を非表示
    list.find('.manage-editor-option-empty').slideUp(function(){
      // 新オプションを表示
      item.slideDown();
    });
    
    // 共通オプション追加ボタンindexを更新
    $(this).data('index', parseInt(index) + 1);
  });
  
  // オプション並び替えを有効化
  $('.manage-editor-option-list').sortable({
    handle: '.manage-moveoption',
    placeholder: 'manage-editor-option-placeholder',
    start: function(event, ui) {
      ui.item.addClass('dragging');
    },
    stop: function(event, ui) {
      ui.item.removeClass('dragging');
      renumberOption($(this));
    },
  });
});