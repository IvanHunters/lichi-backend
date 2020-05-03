<?php
namespace App\Events;

use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class WebsocketHandler implements MessageComponentInterface
{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $connection)
    {
      return 'asdas';
    }

    public function onClose(ConnectionInterface $connection)
    {
        // TODO: Implement onClose() method.
    }

    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    public function onMessage(ConnectionInterface $connection, MessageInterface $msg)
    {
      $numRecv = count($this->clients) - 1;
      echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
          , $connection->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

      foreach ($this->clients as $client) {
          if ($from !== $client) {
              // The sender is not the receiver, send to each client connected
              $client->send($msg);
          }
      }
    }
}
