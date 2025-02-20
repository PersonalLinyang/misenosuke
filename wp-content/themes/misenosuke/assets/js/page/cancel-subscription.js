$(document).ready(function(){  
  // hiddenに入れている契約情報を取得する
  var plan_price = parseInt($('#hidden-plan-price').val());
  var current_period_start = new Date($('#hidden-current-period-start').val());
  var current_period_end = new Date($('#hidden-current-period-end').val());
  
  // 返金金額計算処理
  var calRefundPrice = function() {
    var current_period_start_time = Math.floor(current_period_start.getTime() / 1000);
    var current_period_end_time = Math.floor(current_period_end.getTime() / 1000);
    var type = $('.cancel-subscription-type-radio:checked').val();
    var end_date = current_period_end;
    var refund_price = 0;
    
    if(type == 'date') {
      if($('.cancel-subscription-datepicker').val()) {
        end_date = new Date($('.cancel-subscription-datepicker').val() + ' 23:59:59');
      }
    } else if(type == 'now') {
      end_date = new Date();
    }
    
    var remaining_period = current_period_end_time - Math.floor(end_date.getTime() / 1000);
    var total_period = current_period_end_time - current_period_start_time;
    var refund_price = Math.floor(plan_price * remaining_period / total_period);
    
    $('.cancel-subscription-refund-price').text(refund_price);
  }
  
  // 次回支払日前日を解約指定日の最終日にする
  var datepicker_maxdate = new Date($('#hidden-current-period-end').val());
  datepicker_maxdate.setDate(datepicker_maxdate.getDate() - 1);
  
  // 日付関連情報を取得
  var today = new Date();
  var change_month = true;
  var change_year = true;
  if(today.getFullYear() == datepicker_maxdate.getFullYear()) {
    change_year = false;
    if(today.getMonth() == datepicker_maxdate.getMonth()) {
      change_month = false;
    }
  }
  
  // カレンダーを有効化
  $(".cancel-subscription-datepicker").datepicker({
    dateFormat: "yy-mm-dd",
    minDate: today,
    maxDate: datepicker_maxdate,
    todayText: ucwords(translations.prev_month),
    prevText: ucwords(translations.prev_month),
    nextText: ucwords(translations.next_month),
    changeMonth: change_month,
    changeYear: change_year,
    showButtonPanel: true,
    beforeShow: function(input, inst) {
      if(!change_month) {
        $(".ui-datepicker-prev").addClass('disable');
        $(".ui-datepicker-next").addClass('disable');
        $(".ui-datepicker-current").addClass('disable');
      }
      inst.dpDiv.addClass('month_year_datepicker');
    },
    onSelect: function(dateText, inst) {
      calRefundPrice();
    }
  });
  
  // 「今日」ボタンのクリックイベントをハンドル
  $(document).on('click', '.ui-datepicker-current', function() {
    var today = new Date();
    $(".cancel-subscription-datepicker").datepicker('setDate', today).datepicker('hide').blur();
    calRefundPrice();
  });
  
  // 解約タイプ選択切り替え
  $('.cancel-subscription-type-radio').change(function(){
    if($(this).val() == 'date') {
      $('.cancel-subscription-datearea').slideDown();
    } else {
      $('.cancel-subscription-datearea').slideUp();
    }
    
    calRefundPrice();
    
    $('#cancel-subscription-submit').addClass('active');
  });
  
  // 解約ボタンをクリックする
  $('#cancel-subscription-submit').on('click', function(){
    var fd = new FormData();
    var form = $('#cancel-subscription-form');
    fd.append('action', 'cancel_subscription');
    $(form.serializeArray()).each(function(i, v) {
      fd.append(v.name, v.value);
    });
    removeAllFormWarning(form);
    
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: fd,
      processData: false,
      contentType: false,
      success: function( response ){
        var res = JSON.parse(response);
        if(res['result'] == true) {
          // スクリプション成功でページを遷移
          window.location.href = res['url'];
        } else {
          $.each(res['errors'], function(key, value) {
            addFormWarning(key, value);
          });
        }
      },
      error: function( response ){
        $('#cancel-subscription-warning-system').show();
      }
    });
  });
});