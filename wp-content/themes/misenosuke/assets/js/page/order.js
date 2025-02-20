

const changeOrderNumber = function(order_uid) {
  $('.order-info-order').val(order_uid);
  $('.order-ordernumber').text(order_uid);
}

const changeOrderPeople = function(order_people) {
  $('.order-info-peoplenumber').val(order_people);
  $('.order-menu-peoplenumber').val(order_people);
}

const activeOrderPeopleSubmit = function(obj) {
  var value = parseInt(obj.val());
  if(value > 0) {
    $('.order-people-submit').addClass('active');
  } else {
    obj.val(0);
    $('.order-people-submit').removeClass('active');
  }
}

const changeOrderTab = function(tab_name) {
  $('.order-tab').hide();
  $('.order-' + tab_name).show();
}

$(document).ready(function(){
  // リアルタイム通信開始
  const socket = new WebSocket(socket_server);
  
  // リアルタイム通信サーバーに登録
  socket.onopen = function() {
    var send_data = {
      type: 'connect',
      communities: {
        'seat': $('.order-info-seat').val(),
        'customer': $('.order-info-customer').val(),
      },
    };
    if($('.order-info-order').val()) {
      send_data['communities']['order'] = $('.order-info-order').val();
    }
    socket.send(JSON.stringify(send_data));
  };
  
  // リアルタイム受信
  socket.onmessage = function(event) {
      // リアルタイム受信データ取得
      var data = JSON.parse(event.data);
      var message = data['message'];
      if(message['type'] == 'join') {
        if(!sessionStorage.getItem('refuse_' + message['applicant'])) {
          $('.order-joinapprove-name').text(message['name']);
          $('.order-joinapprove-applicant').val(message['applicant']);
          showPopupSection($('.order-joinapprove-popup'));
        }
      } else if(message['type'] == 'join_approve') {
        if(message['result'] == '1') {
          var fd = new FormData();
          fd.append('action', 'save_ordersession_customer');
          fd.append('seat', $('.order-info-seat').val());
          fd.append('order', message['order']);
          
          $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: fd,
            processData: false,
            contentType: false,
            success: function( response ){
              var res = JSON.parse(response);
              if(res['result'] == true) {
                changeOrderNumber(message['order']);
                changeOrderTab('people');
                hidePopupSection();
                showPopupMessage(ucfirst(translations.ajax_join_order_success), 'success');
                
                var send_data = {
                  type: 'connect',
                  communities: {
                    'order': message['order'],
                  },
                };
                socket.send(JSON.stringify(send_data));
              } else {
                showPopupMessage(res['error'], 'error');
              }
            },
            error: function( response ){
              showPopupMessage(ucfirst(translations.ajax_join_order_failed), 'error');
            }
          });
        } else {
          showPopupMessage(ucfirst(translations.ajax_join_order_refused), 'error');
        }
      } else if(message['type'] == 'people_number') {
        changeOrderTab('menu');
        changeOrderPeople(message['people']);
        showPopupMessage(ucfirst(translations.people_number_is_updated), 'success');
      }
  };
  
  $('.order-header-language-handler').click(function(){
    $(this).toggleClass('active');
    $('.order-header-language-menu').fadeToggle(300);
  });
  
  $('.order-header-handler').click(function(){
    $(this).toggleClass('active');
    $('.order-header-menu').fadeToggle(300);
  });
  
  $('.order-header-menu-shadow').click(function(){
    $('.order-header-handler').removeClass('active');
    $('.order-header-menu').fadeOut(300);
  });
  
  $('.order-header-menu-title.has-sub').click(function(){
    $(this).toggleClass('active');
    $(this).closest('.order-header-menu-item').find('.order-header-menu-sublist').slideToggle(300);
  });
  
  $('.order-top-startorder').click(function(){
    var fd = new FormData();
    fd.append('action', 'start_order_customer');
    fd.append('seat', $('.order-info-seat').val());
    
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: fd,
      processData: false,
      contentType: false,
      success: function( response ){
        var res = JSON.parse(response);
        if(res['result'] == true) {
          if(res['count']) {
            $('.order-join-number').find('option').not('.hidden').remove();
            $.each(res['orders'], function(index, value){
              $('.order-join-number').append('<option value="' + value + '">' + value + '</option>');
            });
            showPopupSection($('.order-join-popup'));
          } else {
            changeOrderNumber(res['order']);
            changeOrderTab('people');
            var send_data = {
              type: 'connect',
              communities: {
                'order': res['order'],
              },
            };
            socket.send(JSON.stringify(send_data));
          }
        } else {
          // エラーメッセージを表示
          showPopupMessage(res['error'], 'error');
        }
      },
      error: function( response ){
        // エラーメッセージを表示
        showPopupMessage(ucfirst(translations.ajax_get_seat_failed), 'error');
      }
    });
  });
  
  $('.order-join-create').click(function(){
    var fd = new FormData();
    fd.append('action', 'create_order_customer');
    fd.append('seat', $('.order-info-seat').val());
    
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: fd,
      processData: false,
      contentType: false,
      success: function( response ){
        var res = JSON.parse(response);
        if(res['result'] == true) {
          changeOrderNumber(res['order']);
          changeOrderTab('people');
          hidePopupSection();
          showPopupMessage(ucfirst(translations.ajax_create_order_success), 'success');
        } else {
          showPopupMessage(res['error'], 'error');
        }
      },
      error: function( response ){
        showPopupMessage(ucfirst(translations.ajax_create_order_failed), 'error');
      }
    });
  });
  
  $('.order-join-join').click(function(){
    $('.order-join-info').slideDown();
  });
  
  $('.order-join-apply').click(function(){
    var validate = true;
    if(!$('.order-join-name').val()) {
      validate = false;
      addFormWarning('join_name', ucfirst(translations.join_name_is_required));
    }
    if(!$('.order-join-number').val()) {
      validate = false;
      addFormWarning('join_order', ucfirst(translations.join_order_is_required));
    }
    if(validate) {
      var send_data = { 
        type: 'message',
        community: $('.order-join-number').val(), 
        message: {
          type: 'join',
          name: $('.order-join-name').val(),
          applicant: $('.order-info-customer').val(), 
        } 
      };
      socket.send(JSON.stringify(send_data));
    }
  });
  
  $('.order-joinapprove-refuse').click(function(){
    if($('.order-joinapprove-remember').prop('checked')) {
      sessionStorage.setItem('refuse_' + $('.order-joinapprove-applicant').val(), 1);
    }
    var send_data = { 
      type: 'message',
      community: $('.order-joinapprove-applicant').val(), 
      message: {
        type: 'join_approve',
        result: '0',
      } 
    };
    socket.send(JSON.stringify(send_data));
    hidePopupSection();
  });
  
  $('.order-joinapprove-approve').click(function(){
    var send_data = { 
      type: 'message',
      community: $('.order-joinapprove-applicant').val(), 
      message: {
        type: 'join_approve',
        result: '1',
        order: $('.order-info-order').val(),
      } 
    };
    socket.send(JSON.stringify(send_data));
    hidePopupSection();
  });
  
  $('.order-people-input').change(function(){
    if(parseInt($(this).val())) {
      $('.order-people-submit').addClass('active');
    } else {
      $('.order-people-submit').removeClass('active');
    }
  });
  
  $('.order-people-input').change(function(){
    activeOrderPeopleSubmit($(this));
  }).on('keyup', function(){
    activeOrderPeopleSubmit($(this));
  }).on('input', function(){
    activeOrderPeopleSubmit($(this));
  });
  
  $('.order-people-submit').click(function(){
    var fd = new FormData();
    var people = $('.order-people-input').val();
    fd.append('action', 'save_peoplenumber_customer');
    fd.append('people', people);
    
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: fd,
      processData: false,
      contentType: false,
      success: function( response ){
        var res = JSON.parse(response);
        if(res['result'] == true) {
          var send_data = {
            type: 'message',
            community: $('.order-info-order').val(), 
            except: 'self',
            message: {
              type: 'people_number',
              people: people,
            } 
          };
          socket.send(JSON.stringify(send_data));
          changeOrderPeople(people);
          changeOrderTab('menu');
          // エラーメッセージを表示
          showPopupMessage(ucfirst(translations.ajax_save_people_success), 'success');
        } else {
          // エラーメッセージを表示
          showPopupMessage(res['error'], 'error');
        }
      },
      error: function( response ){
        // エラーメッセージを表示
        showPopupMessage(ucfirst(translations.ajax_save_people_failed), 'error');
      }
    });
  });
  
  $('.order-menu-category-slidehandler').click(function(){
    $(this).toggleClass('active');
    $(this).closest('.order-menu-category').find('.order-menu-category-body').slideToggle();
  });
  
  $('.order-menu-spinner-number').change(function(){
    var value = parseInt($(this).val());
    if(value) {
      $(this).closest('.order-menu-info').find('.order-menu-option-list').slideDown();
    } else {
      $(this).closest('.order-menu-info').find('.order-menu-option-list').slideUp();
    }
  });
  
  $('.order-menu-peoplenumber').change(function(){
    if($(this).prop('checked')) {
      $(this).closest('.order-menu-info').find('.order-menu-option-list').slideDown();
    } else {
      $(this).closest('.order-menu-info').find('.order-menu-option-list').slideUp();
    }
  });
});