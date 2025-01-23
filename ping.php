<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use GRPC\Pinger\PingRequest;
use GRPC\PingClient;

$client = new PingClient('127.0.0.1:9999', [
    'credentials' => \Grpc\ChannelCredentials::createInsecure(),
]);

$message = new PingRequest();
$message->setUrl('https://www.google.com');

[$response, $status] = $client->Ping($message)->wait();

echo $response->serializeToJsonString() . PHP_EOL;
