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
    private string $hostname = 'mysymfonyapi.com:8080';

    public function __construct(
        private readonly ClientInterface $httpClient = new Client(),
    ) {
        $this->grpcClient = new PingerGrpcClient($this->hostname, [
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
            $message = new PingRequest();
            $httpResponse = $this->httpClient->post($this->hostname . '/ping', [
                'body' => $message->serializeToJsonString(),
            ]);
            $statusCodes[] = $httpResponse->getStatusCode();
        }

        return new JsonResponse(['REST Ping status codes' => $statusCodes]);
    }

    #[Route('/ping/grpc')]
    public function grpcPing(
        #[MapQueryParameter] int $count = 1
    )
    {
        $statusCodes = [];
        for ($i = 0; $i < $count; $i++) {
            $message = new PingRequest();
            [$response, $status] = $this->grpcClient->Ping($message)->wait();

            $statusCodes[] = $response?->getStatusCode() ?? null;
        }

        return new JsonResponse([
            'GRPC Ping status codes' => $statusCodes,
        ]);
    }
}
