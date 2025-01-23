<?php

declare(strict_types=1);

namespace GRPC;

use GRPC\Pinger\PingRequest;
use GRPC\Pinger\PingResponse;

class PingClient extends \Grpc\BaseStub
{
    public function Ping(PingRequest $message, $metadata = [], $options = [])
    {
        return $this->_simpleRequest(
            '/pinger.Pinger/ping',
            $message,
            [PingResponse::class, 'decode'],
            $metadata,
            $options
        );
    }
}