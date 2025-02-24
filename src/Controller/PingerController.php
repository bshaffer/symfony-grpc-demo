<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\ValueResolver\ContextResolver;
use App\ValueResolver\ProtobufMessageResolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use GRPC\Pinger\PingerInterface;
use GRPC\Pinger\PingRequest;
use GRPC\Pinger\PingResponse;
use GRPC\Pinger\PingerGrpcClient;
use Spiral\RoadRunner\GRPC\ContextInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;


class PingerController implements PingerInterface
{
    private int $count = 100;
    private PingerGrpcClient $grpcClient;

    public function __construct(
        private readonly ClientInterface $httpClient = new Client(),
    ) {
        $this->grpcClient = new PingerGrpcClient('127.0.0.1:9999', [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);
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
        // $httpResponse = $this->httpClient->request('GET', $in->getUrl());
        // $statusCode = $httpResponse->getStatusCode();
        $statusCode = 200;

        return new PingResponse(['status_code' => $statusCode]);
    }

    #[Route('/ping/rest')]
    public function restPing()
    {
        $statusCodes = [];
        for ($i = 0; $i < $this->count; $i++) {
            $statusCodes[] = $this->doRestPing();
        }

        return new JsonResponse([
            'REST Ping status codes' => $statusCodes,
        ]);
    }

    private function doRestPing(): int
    {
        $message = new PingRequest();
        $httpResponse = $this->httpClient->request(
            'POST',
            'http://localhost:8080/ping',
            [
                'body' => $message->serializeToJsonString(),
            ]
        );
        return $httpResponse->getStatusCode();
    }

    #[Route('/ping/grpc')]
    public function grpcPing()
    {
        $statusCodes = [];
        for ($i = 0; $i < $this->count; $i++) {
            $statusCodes[] = $this->doGrpcPing();
        }

        return new JsonResponse([
            'GRPC Ping status codes' => $statusCodes,
        ]);
    }

    private function doGrpcPing(): int
    {
        $message = new PingRequest();
        [$response, $status] = $this->grpcClient->Ping($message)->wait();

        return $response?->getStatusCode() ?? 0;
    }
}
