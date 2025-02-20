// 文言最初の文字を大文字に変換
function ucfirst(str) {
  if (str.length === 0) {
    return str;
  }
  return str.charAt(0).toUpperCase() + str.slice(1);
}

// 単語ごと最初の文字を大文字に変換
function ucwords(str) {
  return str.replace(/\b\w/g, function(match) {
    return match.toUpperCase();
  });
}

// フォームエラー表示
const addFormWarning = function(key, text) {
  $('.warning-' + key).html(text);
  $('.warning-' + key).closest('.form-input').find('[name="' + key + '"]').addClass('error');
  $('.warning-' + key).closest('.form-input').find('.input').addClass('error');
  $('.warning-' + key).closest('.form-input').find('.checkbox-check').addClass('error');
  $('.warning-' + key).slideDown();
}

// フォームエラー解消
const removeFormWarning = function(obj) {
  obj.closest('.form-input').find('.error').removeClass('error');
  obj.closest('.form-input').find('.warning').slideUp();
}

// フォームエラー全部解消
const removeAllFormWarning = function(form) {
  form.find('.error').removeClass('error');
  form.find('.warning').slideUp();
}

// ポップアップ枠を表示
const showPopupSection = function(section) {
  $('.popup-shadow').fadeIn();
  section.fadeIn();
}

// ポップアップ枠を酉治
const hidePopupSection = function() {
  $('.popup-shadow').fadeOut();
  $('.popup-section').fadeOut();
}

// ポップアップメッセージを表示
const showPopupMessage = function(message, type='success') {
  $('.popup-message-text').removeClass('success').removeClass('error').removeClass('warning').removeClass('info').text(message).addClass(type);
  $('.popup-message').fadeIn(function(){
    setTimeout(function(){
      $('.popup-message').fadeOut();
    }, 3000);
  });
}

// チェックボックス動作を初期化
const initCheckbox = function(obj) {
  if(obj.prop('checked')) {
    obj.closest('.checkbox-check').addClass('active');
  } else {
    obj.closest('.checkbox-check').removeClass('active');
  }
}

// スパイナー有効化
const initSpinner = function(obj) {
  const number = obj.find('[type="number"]');
  
  obj.find('.spinner-minus').click(function(){
    const value = parseInt(number.val());
    if(number.attr('min') === undefined) {
      number.val(value - 1).change();
    } else {
      const min = parseInt(number.attr('min'));
      if(value >= min + 1) {
        number.val(value - 1).change();
      }
    }
  });
  
  obj.find('.spinner-plus').click(function(){
    const value = parseInt(number.val());
    if(number.attr('max') === undefined) {
      number.val(value + 1).change();
    } else {
      const max = parseInt(number.attr('max'));
      if(value <= max - 1) {
        number.val(value + 1).change();
      }
    }
  });
}

$(document).ready(function(){
  // 言語選択をクリックすると選択肢を広げる、以外の部分をクリックすると選択肢を閉じる
  $(document).on('click',function(e) {
    if($(e.target).closest('.header-language-current').length) {
      $('.header-language').find('.header-language-list').slideToggle();
    } else {
      $('.header-language').find('.header-language-list').slideUp();
    }
  });
  
  // [SP]ヘッダーメニューハンドラーをクリック
  $('.header-menu-handler').click(function(){
    if($(this).hasClass('active')) {
      $(this).removeClass('active');
      $('.header-menu').slideUp();
    } else {
      $(this).addClass('active');
      $('.header-menu').slideDown();
    }
  });
  
  // パスワードを表示するボタンをクリック
  $('.password-show').click(function(){
    if($(this).hasClass('active')) {
      $(this).removeClass('active');
      $(this).closest('.password').find('input[type="text"]').attr('type', 'password');
    } else {
      $(this).addClass('active');
      $(this).closest('.password').find('input[type="password"]').attr('type', 'text');
    }
  });
  
  // チェックボックスクリック
  $('.checkbox-check').find('input[type="checkbox"]').on('change', function(){
    initCheckbox($(this));
  });
  
  // スパイナー有効化
  $('.spinner').each(function(){ 
    initSpinner($(this)); 
  });
  
  // ポップアップシャドークリック
  $('.popup-shadow').click(function(){
    hidePopupSection();
  });
  
  // ポップアップ閉じるボタンクリック
  $('.popup-close').click(function(){
    hidePopupSection();
  });
});