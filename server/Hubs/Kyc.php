<?php

declare(strict_types=1);

namespace Digidocs\Hubs;

use \Digidocs\WebSocket\Application\Application;
use \Digidocs\WebSocket\Connection;

class Kyc extends Application
{ 
    private array $clients = [];
    private array $nicknames = [];

    public function onConnect(Connection $connection): void
    {
        $id = $connection->getClientId();
        $this->clients[$id] = $connection;
        // $this->nicknames[$id] = 'Guest' . rand(10, 999);
        $connection->log($id.' : is connected.');
    }
 
    public function onDisconnect(Connection $connection): void
    {
        $id = $connection->getClientId();
        unset($this->clients[$id], $this->nicknames[$id]);
    }

    
    public function onData(string $data, Connection $client): void
    {
        try {
 
            $decodedData = $this->decodeData($data);
  
            $message = $decodedData['data'] ?? '';
            if ($message === '') {
                return;
            }
            $idn = $decodedData['data'] ?? '';
            if ($decodedData['action'] !== 'idn') {
                if(key_exists($message,$this->clients)){
                    $this->clients[$idn]->close();  
                }
                $this->clients[$idn] = $client;
                $clientId = $client->getClientId();
            }
 
            $clientId = $client->getClientId();
            // $message = $this->nicknames[$clientId] . ': ' . $message;
            $client->log($message);
            // $this->actionEcho($message);
        } catch (\RuntimeException $e) {
            // @todo Handle/Log error
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
