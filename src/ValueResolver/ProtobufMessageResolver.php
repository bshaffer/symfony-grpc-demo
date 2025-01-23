<?php

// src/ValueResolver/IdentifierValueResolver.php
namespace App\ValueResolver;

use GRPC\Pinger\PingRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ProtobufMessageResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $message = new PingRequest();
        if ($body = $request->getContent()) {
            $message->mergeFromJsonString((string) $body);
        }
        return [$message];
    }
}