<?php

// src/ValueResolver/IdentifierValueResolver.php
namespace App\ValueResolver;

use Spiral\RoadRunner\GRPC\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ContextResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        return [new Context([])];
    }
}