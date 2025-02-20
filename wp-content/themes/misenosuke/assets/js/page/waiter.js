const getUrlParameters = function(url) {
    var params = {};
    var parser = document.createElement('a');
    parser.href = url;
    var queryString = parser.search.substring(1); // クエリストリングの先頭の?を取り除く
    var queries = queryString.split("&");

    for (var i = 0; i < queries.length; i++) {
        var pair = queries[i].split("=");
        var key = decodeURIComponent(pair[0]);
        var value = decodeURIComponent(pair[1] || ''); // パラメータが存在しない場合は空文字列

        params[key] = value;
    }
    return params;
}


const initWaiterScan = function() {
  var video = document.getElementById('waiter-popup-scan-video');
  var seat = $('.waiter-popup-scan-seat');
  
  // カメラのストリームを取得
  navigator.mediaDevices.getUserMedia({ video: true }).then(function (stream) {
    video.srcObject = stream;
    video.play();
    
    // ビデオがロードされたらQRコードスキャンのループを開始
    video.addEventListener('loadeddata', function() {
      scanQRCode(); // 最初のスキャンを開始
    });
  }).catch(function (error) {
    console.error('カメラにアクセスできません:', error);
  });
  
  // QRコードスキャン関数
  function scanQRCode() {
    // ビデオのコンテキストを取得
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const context = canvas.getContext('2d');

    // ビデオのフレームをキャプチャ
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // QRコードをスキャン
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height);

    if (code) {
      // QRコードが検出された場合、結果を表示
      var params = getUrlParameters(code.data);
      if(params['seat_uid'] !== undefined && params['seat_uid'] != seat.val()) {
        seat.val(params['seat_uid']).trigger('change');
      }
    }

    // 次のフレームをスキャン
    requestAnimationFrame(scanQRCode);
  }
}


$(document).ready(function(){
  // WebSocketサーバーを初期化
  var socket = new WebSocket(socket_server);
  
  socket.onopen = function() {
    console.log('WebSocket connected');
    var registerData = {
      type: 'connect',
      group_uid: restaurant_uid,
      client_uid: user_uid
    };
    socket.send(JSON.stringify(registerData));
  };
  
  socket.onmessage = function(event) {
    var data = JSON.parse(event.data);
    // JSONデータの内容を画面上に表示する例
    alert('Received JSON data: ' + JSON.stringify(data));
  };
  
  $('.waiter-header-language-handler').click(function(){
    $(this).toggleClass('active');
    $('.waiter-header-language-menu').fadeToggle(300);
  });
  
  $('.waiter-header-handler').click(function(){
    $(this).toggleClass('active');
    $('.waiter-header-menu').fadeToggle(300);
  });
  
  $('.waiter-header-menu-shadow').click(function(){
    $('.waiter-header-handler').removeClass('active');
    $('.waiter-header-menu').fadeOut(300);
  });
  
  $('.waiter-header-menu-title.has-sub').click(function(){
    $(this).toggleClass('active');
    $(this).closest('.waiter-header-menu-item').find('.waiter-header-menu-sublist').slideToggle(300);
  });
  
  initWaiterScan();
  
  $('.waiter-footer-button-scan').click(function(){
    $('.waiter-popup-scan-seat').val('');
    $('.waiter-popup-scan-result').text('');
    $('.popup-shadow').fadeIn();
    $('.waiter-popup-scan').fadeIn();
  });
  
  $('.waiter-popup-scan-seat').change(function(){
    if($(this).val()) {
      $.ajax({
        type: 'GET',
        url: ajaxurl + '?action=get_seat_simple_waiter&seat=' + $(this).val(),
        processData: false,
        contentType: false,
        success: function( response ){
          var res = JSON.parse(response);
          if(res['result'] == true) {
            // 席情報を表示
            $('.waiter-popup-scan-result').text(res['name']);
            $('.waiter-popup-scan-confirm').addClass('active');
          } else {
            // エラーメッセージを表示
            showPopupMessage(res['error'], 'error');
          }
        },
        error: function( response ){
          // エラーメッセージを表示
          showPopupMessage(translations.ajax_get_seat_failed, 'error');
        }
      });
    }
  });
  
  $('.waiter-popup-scan-confirm').click(function(){
    if($(this).hasClass('active')) {
      $.ajax({
        type: 'GET',
        url: ajaxurl + '?action=get_seat_detail_waiter&seat=' + $('.waiter-popup-scan-seat').val(),
        processData: false,
        contentType: false,
        success: function( response ){
          var res = JSON.parse(response);
          if(res['result'] == true) {
            
          } else {
            // エラーメッセージを表示
            showPopupMessage(res['error'], 'error');
          }
        },
        error: function( response ){
          // エラーメッセージを表示
          showPopupMessage(translations.ajax_get_seat_failed, 'error');
        }
      });
    }
  });
  
  $('.waiter-seat-order-qr').click(function(){
    var seat = $(this).closest('.waiter-seat-item').data('seat');
    var order = $(this).closest('.waiter-seat-order-item').data('order');
    var url = lang_domain + '/order/?seat_uid=' + seat + '&order_uid=' + order; // 指定のURL
    $('#waiter-popup-order-qr').empty(); // QRコードを生成する前にクリア
    new QRCode(document.getElementById("waiter-popup-order-qr"), {
        text: url,
        width: 128,
        height: 128
    });
    $('.popup-shadow').fadeIn();
    $('.waiter-popup-order').fadeIn();
  });
});