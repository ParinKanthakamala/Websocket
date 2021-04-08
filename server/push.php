<?php

require __DIR__ . './Websocket/IPCPayload.php';
require __DIR__ . './Websocket/IPCPayloadFactory.php';
require __DIR__ . './Websocket/PushClient.php';

/**
 * This code shows how to push data into the running websocket server.
 * In this case a system message is sent to the chat demo application.
 */

$pushClient = new \Digidocs\WebSocket\PushClient( '//tmp/phpwss.sock' );
$pushClient->sendToApplication( 'chat', [
    'action' => 'echo',
    'data'   => 'Hello from the PushClient!',
] );
