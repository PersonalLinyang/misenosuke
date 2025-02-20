
$(document).ready(function(){
var socket = new WebSocket(socket_server); // WebSocketサーバーのアドレスに合わせて変更する
  
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

// ボタンクリック時の処理（JSONを送信）
document.getElementById('sendJsonButton').addEventListener('click', function() {
    var jsonData = { 
        type: 'message',
        client_to: '78e4715ff93b831d90f58ceb368c108d', 
        message: 'Hello, WebSocket!' 
    }; // 送信するJSONデータを作成
    socket.send(JSON.stringify(jsonData));
});


});