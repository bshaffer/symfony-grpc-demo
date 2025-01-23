<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\ValueResolver\ContextResolver;
use App\ValueResolver\ProtobufMessageResolver;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use GRPC\Pinger\PingerInterface;
use GRPC\Pinger\PingRequest;
use GRPC\Pinger\PingResponse;
use Spiral\RoadRunner\GRPC\ContextInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class PingerController implements PingerInterface
{
    public function __construct(
        private readonly ClientInterface $httpClient = new Client()
    ) {
    }

    #[Route('/lucky/number')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response(
            '<html><body>Lucky number: '.$number.'</body></html>'
        );
    }

    #[Route('/ping')]
    public function ping(
        #[ValueResolver(ContextResolver::class)]
        ContextInterface $ctx,
        #[ValueResolver(ProtobufMessageResolver::class)]
        PingRequest $in
    ): PingResponse {
        $httpResponse = $this->httpClient->request('GET', $in->getUrl());

        return new PingResponse(['status_code' => $httpResponse->getStatusCode()]);
    }
}
