<?php

namespace Athorrent\Ipc\Socket;

abstract class UnixSocket implements SocketInterface
{
    protected $socket;

    public function shutdown(): void
    {
        socket_shutdown($this->socket, 2);
    }

    public function close(): void
    {
        socket_close($this->socket);
    }
}
