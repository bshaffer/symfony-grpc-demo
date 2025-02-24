<?php

namespace App\EventSubscriber;

use App\Controller\TokenAuthenticatedController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Google\Protobuf\Internal\Message;
use GRPC\Pinger\PingerInterface;

class TokenSubscriber implements EventSubscriberInterface
{
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        // when a controller class defines multiple action methods, the controller
        // is returned as [$controllerInstance, 'methodName']
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof PingerInterface) {
        }
    }

    // public function onKernelResponse(ResponseEvent $event): void
    // {
        // $response = $event->getResponse();

        // $event->setResponse(new Response(
        //     $response->serializeToJsonString(),
        //     Response::HTTP_OK,
        //     ['content-type' => 'application/json']
        // ));
    // }

    public function onKernelView(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if ($result instanceof Message) {
            $event->setResponse(new Response(
                $result->serializeToJsonString(),
                Response::HTTP_OK,
                ['content-type' => 'application/json']
            ));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // KernelEvents::CONTROLLER => 'onKernelController',
            // KernelEvents::RESPONSE => 'onKernelResponse',
            KernelEvents::VIEW => 'onKernelView',
        ];
    }
}
