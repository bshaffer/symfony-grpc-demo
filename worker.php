<?php

/**
 * RoadRunner PHP server for gRPC and REST.
 */

use GRPC\Pinger\PingerInterface;
use App\Controller\PingerController;
use Spiral\RoadRunner\GRPC\Invoker;
use Spiral\RoadRunner\GRPC\Server;
use Spiral\RoadRunner\Worker;

switch (getenv('RR_MODE')) {
    case 'http':
        return require __DIR__ . '/public/index.php';
    case 'grpc':
        require __DIR__ . '/vendor/autoload.php';
        $worker = Worker::create();
        $server = new Server(new Invoker(), [
            'debug' => true, // optional (default: false)
        ]);

        $server->registerService(PingerInterface::class, new PingerController());

        $server->serve($worker);
    default:
        throw new \Exception(sprintf('Invalid RR_MODE "^%s"', getenv('RR_MODE')));
}