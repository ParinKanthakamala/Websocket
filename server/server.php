<?php

use Digidocs\WebSocket\Application\StatusApplication;
use \Digidocs\Hubs\Chat;
use \Digidocs\WebSocket\Server;

require './vendor/autoload.php';

$server = new Server( '127.0.0.1', 8000, '/tmp/phpwss.sock' );

// maxx : add a PSR-3 compatible logger (optional)
$server->setLogger( new \Digidocs\WebSocket\Logger\StdOutLogger() );

// maxx : server settings
$server->setMaxClients( 100 );
$server->setCheckOrigin( false );
$server->setAllowedOrigin( 'foo.lh' );
$server->setMaxConnectionsPerIp( 100 );

// maxx : add hub application add your applications
$server->registerApplication( 'status', StatusApplication::getInstance() );
$server->registerApplication( 'chat', Chat::getInstance() );
$server->run();

