<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Amp\Delayed;
use Amp\Websocket;

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/amphp/amp/lib/Loop.php';

class WsTransaction extends Model
{
    public function index()
    {
		Loop::run(function () {
		    /** @var Websocket\Connection $connection */
		    
		    
		    $connection = yield Websocket\connect('wss://mainnet.infura.io/ws');
		    yield $connection->send('{"jsonrpc":"2.0", "id": 1, "method": "eth_subscribe", "params": ["logs", {"address": "0x39755357759cE0d7f32dC8dC45414CCa409AE24e"}]}');

		    /** @var Websocket\Message $message */
		    while ($message = yield $connection->receive()) {
		        $payload = yield $message->buffer();

		        printf("Received: %s\n", $payload);

		    }
		});

    }
}


