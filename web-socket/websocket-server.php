<?php

/*
 * リアルタイムに情報通信を行うためのWebSocketファイル
 * 利用方法：
 *    xamppの場合：C:\xampp\htdocs\websocket-server.phpそして保存して、コマンド行でphpコマンドで実行（php C:\xampp\htdocs\websocket-server.php）
 */

// websocket-server.php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';

class MyWebSocketServer implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->community = array();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection: {$conn->resourceId}\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Message sent: {$msg}\n";
        $data = json_decode($msg, true);

        if (isset($data['type']) && $data['type'] === 'connect') {
            // クライアントIDを登録
            if(isset($data['communities'])) {
              foreach($data['communities'] as $key => $uid) {
                if(!array_key_exists($uid, $this->community)) {
                    $this->community[$uid] = array();
                }
                array_push($this->community[$uid], $from);
                echo "Client registered: {$key}: {$uid}({$from->resourceId})\n";
              }
            }
        } elseif (isset($data['type']) && $data['type'] === 'message') {
            // 特定のグループのクライアントにメッセージを送信
            if(isset($data['community'])) {
                if (isset($this->community[$data['community']])) {
                    foreach ($this->community[$data['community']] as $client) {
                        if($client != $from) {
                            $client->send($msg);
                            echo "Message sent to: {$data['community']}\n";
                        }
                    }
                }
            }
        } else {
            // 全クライアントにメッセージを送信
            foreach ($this->clients as $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        // コミュニティリストから除外
        foreach($this->community as $uid => $community_clients) {
            $client_index = array_search($conn, $community_clients);
            if($client_index !== false) {
                unset($this->community[$uid][$client_index]);
            }
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MyWebSocketServer()
        )
    ),
    8080 // WebSocketサーバーのポート番号
);

echo "WebSocket server running at port 8080...\n";

$server->run();
