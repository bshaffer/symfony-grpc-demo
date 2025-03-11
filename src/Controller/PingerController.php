<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\ValueResolver\ContextResolver;
use App\ValueResolver\ProtobufMessageResolver;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
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
    private PingerGrpcClient $grpcClient;

    public function __construct(
        private readonly ClientInterface $httpClient = new Client(),
    ) {
        $this->grpcClient = new PingerGrpcClient('mysymfonyapi.com:8080', [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);
    }

    #[Route('/ping')]
    public function ping(
        #[ValueResolver(ContextResolver::class)]
        ContextInterface $ctx,
        #[ValueResolver(ProtobufMessageResolver::class)]
        PingRequest $in
    ): PingResponse {
        $statusCode = 200;

        return new PingResponse(['status_code' => $statusCode]);
    }

    #[Route('/ping/rest')]
    public function restPing(
        #[MapQueryParameter] int $count = 1
    ) {
        $statusCodes = [];
        for ($i = 0; $i < $count; $i++) {
            $statusCodes[] = $this->doRestPing();
        }

        return new JsonResponse(['REST Ping status codes' => $statusCodes]);
    }

    private function doRestPing(): int
    {
        $message = new PingRequest();
        $httpResponse = $this->httpClient->request(
            'POST',
            'http://mysymfonyapi.com:8080/ping',
            [
                'body' => $message->serializeToJsonString(),
            ]
        );
        return $httpResponse->getStatusCode();
    }

    #[Route('/ping/grpc')]
    public function grpcPing(
        #[MapQueryParameter] int $count = 1
    )
    {
        $statusCodes = [];
        for ($i = 0; $i < $count; $i++) {
            $statusCodes[] = $this->doGrpcPing();
        }

        return new JsonResponse([
            'GRPC Ping status codes' => $statusCodes,
        ]);
    }

    private function doGrpcPing(): int|null
    {
        $message = new PingRequest();
        [$response, $status] = $this->grpcClient->Ping($message)->wait();

        return $response?->getStatusCode() ?? null;
    }
}
