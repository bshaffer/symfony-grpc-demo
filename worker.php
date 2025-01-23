<?php

/**
 * Sample GRPC PHP server.
 */

use GRPC\Pinger\PingerInterface;
use App\Controller\PingerController;
use Spiral\RoadRunner\GRPC\Invoker;
use Spiral\RoadRunner\GRPC\Server;
use Spiral\RoadRunner\Worker;

require __DIR__ . '/vendor/autoload.php';

$server = new Server(new Invoker(), [
    'debug' => true, // optional (default: false)
]);

$server->registerService(PingerInterface::class, new PingerController());

$server->serve(Worker::create());
