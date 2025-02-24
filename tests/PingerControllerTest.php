<?php

namespace App\Tests\Controller;

use App\Controller\PingerController;
use App\ValueResolver\ContextResolver;
use App\ValueResolver\ProtobufMessageResolver;
use GRPC\Pinger\PingRequest;
use GRPC\Pinger\PingResponse;
use GRPC\Pinger\PingerGrpcClient;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Psr7\Response as Psr7Response;

class PingerControllerTest extends TestCase
{
    use ProphecyTrait;

    private $httpClient;
    private $grpcClient;
    private $controller;

    protected function setUp(): void
    {
        $this->httpClient = $this->prophesize(ClientInterface::class);
        $this->grpcClient = $this->prophesize(PingerGrpcClient::class);
        $this->controller = new PingerController($this->httpClient->reveal());
        $reflection = new \ReflectionProperty($this->controller, 'grpcClient');
        $reflection->setAccessible(true);
        $reflection->setValue($this->controller, $this->grpcClient->reveal());
    }

    public function testNumber()
    {
        $response = $this->controller->number();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertStringContainsString('Lucky number:', $response->getContent());
    }

    public function testPing()
    {
        $ctx = $this->prophesize(ContextInterface::class)->reveal();
        $in = new PingRequest();

        $response = $this->controller->ping($ctx, $in);

        $this->assertInstanceOf(PingResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRestPing()
    {
        $mockResponse = new Psr7Response(200);
        $this->httpClient->request(Argument::any(), Argument::any())->willReturn($mockResponse);
        $response = $this->controller->restPing();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('REST Ping status codes', $data);
        $this->assertCount(100, $data['REST Ping status codes']);
        $this->assertEquals(200, $data['REST Ping status codes'][0]);
    }

    public function testGrpcPing()
    {
        $mockResponse = new PingResponse(['status_code' => 200]);
        $promise = $this->prophesize(\Grpc\UnaryCall::class);
        $promise->wait()->willReturn([$mockResponse, null]);
        $this->grpcClient->Ping(Argument::any())->willReturn($promise->reveal());

        $response = $this->controller->grpcPing();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('GRPC Ping status codes', $data);
        $this->assertCount(100, $data['GRPC Ping status codes']);
        $this->assertEquals(200, $data['GRPC Ping status codes'][0]);
    }

    public function testGrpcPingNoResponse()
    {
        $promise = $this->prophesize(\Grpc\UnaryCall::class);
        $promise->wait()->willReturn([null, null]);
        $this->grpcClient->Ping(Argument::any())->willReturn($promise->reveal());

        $response = $this->controller->grpcPing();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('GRPC Ping status codes', $data);
        $this->assertCount(100, $data['GRPC Ping status codes']);
        $this->assertEquals(0, $data['GRPC Ping status codes'][0]);
    }
}
