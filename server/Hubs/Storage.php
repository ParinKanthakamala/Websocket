<?php

declare(strict_types=1);

namespace Digidocs\Hubs;

use \Digidocs\WebSocket\Application\Application;
use \Digidocs\WebSocket\Connection;

class Storage extends Application
{ 
    private array $clients = [];
    private array $nicknames = [];

    public function onConnect(Connection $connection): void
    {
        $id = $connection->getClientId();
        $this->clients[$id] = $connection;
        $this->nicknames[$id] = 'Guest' . rand(10, 999);
    }
 
    public function onDisconnect(Connection $connection): void
    {
        $id = $connection->getClientId();
        unset($this->clients[$id], $this->nicknames[$id]);
    }

    private function log($message,$file="log.txt"){
       
// Open the file to get existing content
// $current = file_get_contents($file);
// Append a new person to the file
// $current .= "John Smith\n";
// Write the contents back to the file
// file_put_contents($file, $current);
    }
    // public function onFile():voie{}
    public function onData(string $data, Connection $client): void
    {
        try {
            
             $client->server->log("test message");
                


        } catch (\RuntimeException $e) {
            // @todo Handle/Log error
            $decodedData = $this->decodeData($data);
 
            $clientId = $client->getClientId();
            $message = $this->nicknames[$clientId] . ': ' . $e->getMessage();
            $this->actionEcho($message);
        }
    }
 
    public function onIPCData(array $data): void
    {
        $actionName = 'action' . ucfirst($data['action']);
        $message = 'System Message: ' . $data['data'] ?? '';
        if (method_exists($this, $actionName)) {
            call_user_func([$this, $actionName], $message);
        }
    }
 
    private function actionEcho(string $text): void
    {
        $encodedData = $this->encodeData('echo', $text);
        foreach ($this->clients as $sendto) {
            $sendto->send($encodedData);
        }
    }
}
