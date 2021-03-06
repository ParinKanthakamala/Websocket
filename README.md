<p align="center">
    <img src="myself.png" width="80px" height="80px">
</p>

<h1 align="center">Parin PHP WebSockets</h1>

<p align="center">
    Simple WebSocket server implemented in PHP.
</p>

- [Installation](#installation)
    - [Requirements](#requirements)
    - [Installation procedure](#installation-procedure)
- [Usage](#usage)
    - [Server](#server)
    - [Applications](#applications)
    - [Timers](#timers)
    - [Push-Client (IPC)](#push-client-ipc)
    - [Client (Browser/JS)](#client-browserjs)
- [Intended use and limitations](#intended-use-and-limitations)
- [Alternatives](#alternatives)
- [License](#license)

## Installation

### Requirements

* PHP >= 7.4
* ext-json
* ext-sockets

<!-- ### Installation procedure -->
<!-- Install the package using composer: -->
<!-- `composer require Parin/php-websocket` -->

## Usage

### Server

After downloading the sourcecode to your machine, you need some code to actually put your websocket server together.
Here is a basic exmaple:
```php
<?php

// require necessary files here
use \WebSocket\Server;
use \WebSocket\Application\StatusApplication;
use \Hubs\Chat;
// create new server instance
$server = new Server('127.0.0.1', 8000, '/tmp/phpwss.sock');

// server settings
$server->setMaxClients(100);
$server->setCheckOrigin(false);
$server->setAllowedOrigin('example.com');
$server->setMaxConnectionsPerIp(20);

// add your applications
$server->registerApplication('status', StatusApplication::getInstance());
$server->registerApplication('chat', Chat::getInstance());

// start the server
$server->run();
```

Assuming this code is in a file called `server/server.php` you can then start your server with the following command:

```shell
php server.php
```

The websocket server will then listen for new connections on the provided host and port. By default, this will be
`localhost:8000`.

This repositoy also includes a working example in [server/server.php](examples/server.php)

### Applications

The websocket server itself handles connections but is pretty useless without any addional logic. This logic is added
by applications. In the example above two applications are added to the server: `status` and `chat`.

The most important methods in your application will be:

```php
interface ApplicationInterface
{
    public function onConnect(Connection $connection): void;

    public function onDisconnect(Connection $connection): void;

    public function onData(string $data, Connection $client): void;

    public function onIPCData(array $data): void;
}
```

`onConnect` and `onDisconnect` can be used to keep track of all the clients connected to your application. `onData` will
be called whenever the websocket server receives new data from one of the clients connected to the application. 
`onIPCData` will be called if data is provided by another process on your machine. (See [Push-Client (IPC)](#push-client-ipc))

A working example of an application can be found in [client/Chat.php](client/Chat.php)

### Timers

A common requirement for long-running processes such as a websocket server is to execute tasks periodically. This can
be done using timers. Timers can execute methods within your server or application periodically. Here is an example:

```php
use \WebSocket\Server;
use \Hubs\Chat;
$server = new Server('127.0.0.1', 8000, '/tmp/phpwss.sock');
$chat = Chat::getInstance();
$server->addTimer(5000, function () use ($chat) {
    $chat->someMethod();
});
$server->registerApplication('chat', $chat);
```

This example would call the method `someMethod` within your chat application every 5 seconds.

### Push-Client (IPC)

It is often required to push data into the websocket-server process from another application. Let's assume you run a
website containing a chat and an area containing news or a blog. Now every time a new article is published in your blog
you want to notify all users currently in your chat. To achieve this you somehow need to push data from your blog
logic into the websocket server. This is where the Push-Client comes into play.

When starting the websocket server, it opens a unix-domain-socket and listens for new messages. The Push-Client can
then be used to send these messages. Here is an example:

```php
use \WebSocket\PushClient;
$pushClient = new PushClient('//tmp/phpwss.sock');
$pushClient->sendToApplication('chat', [
    'action' => 'echo',
    'data' => 'New blog post was published!',
]);
```

This code pushes data into your running websocket-server process. In this case the `echo` method within the
chat-application is called and it sends the provided message to all connected clients.

You can find the full working example in: [server/push.php](server/push.php)

**Important Hint:** Push messages cannot be larger than 64kb!

### Client (Browser/JS)

Everything above this point was related to the server-side of things. But how to connect to the server from your browser?

Here is a simple example:

```html
<script>
 // connect to chat application on server
let serverUrl = 'ws://<host name or ip>:8000/<hub name>';
let socket = new WebSocket(serverUrl);

// log new messages to console
socket.onmessage = (msg) => {
    let response = JSON.parse(msg.data);
    console.log(response.data);
};
</script>
```

This javascript connects to the chat application on your server and prints all incoming messages into the console.

A better example of the chat client can be found in: [client/chat.html](client/chat.html)

## Intended use and limitations

This project was mainly built for educational purposes. The code is relatively simple and easy to understand. This
server was **not tested in production**, so I strongly recommend not to use it in a live project. It should be totally
fine for small educational projects or internal tools, but most probably will not handle huge amounts of traffic or
connections very well.

Also, some "features" are missing by design:

* SSL is not supported. If required, you can use a reverse proxy like nginx.
* Binary messages are not supported.
* A lot of other stuff I did not even realize ;)

In case you need a more "robust" websocket server written in PHP, please have a look at the excellent alternatives
listed below.
 