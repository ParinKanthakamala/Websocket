<?php

declare(strict_types=1);

namespace Hubs;

use \WebSocket\Application\Application;
use \WebSocket\Connection;

class Chat extends Application
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

    
    public function onData(string $data, Connection $client): void
    {
        try {
            $decodedData = $this->decodeData($data);

            // check if action is valid
            if ($decodedData['action'] !== 'echo') {
                return;
            }

            $message = $decodedData['data'] ?? '';
            if ($message === '') {
                return;
            }

            $clientId = $client->getClientId();
            $message = $this->nicknames[$clientId] . ': ' . $message;

            $client->log($message);

            $this->actionEcho($message);
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
