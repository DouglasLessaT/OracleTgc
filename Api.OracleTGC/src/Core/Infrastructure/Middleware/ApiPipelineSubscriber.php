<?php

namespace App\Core\Infrastructure\Middleware;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Middleware de pipeline da API.
 * Adiciona request ID (rastreamento), headers padrão e facilita a comunicação
 * entre cliente e servidor de forma unificada.
 */
class ApiPipelineSubscriber implements EventSubscriberInterface
{
    public const REQUEST_ID_ATTRIBUTE = 'app.request_id';
    public const REQUEST_ID_HEADER = 'X-Request-Id';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 256],
            KernelEvents::RESPONSE => ['onKernelResponse', -256],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Request ID: usa o enviado pelo cliente ou gera um novo (rastreamento entre sistemas)
        $requestId = $request->headers->get(self::REQUEST_ID_HEADER)
            ?? $this->generateRequestId();
        $request->attributes->set(self::REQUEST_ID_ATTRIBUTE, $requestId);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$response) {
            return;
        }

        $requestId = $request->attributes->get(self::REQUEST_ID_ATTRIBUTE);
        if ($requestId) {
            $response->headers->set(self::REQUEST_ID_HEADER, $requestId);
        }

        // Headers padrão para APIs (facilita consumo por outros sistemas)
        if (!$response->headers->has('Content-Type') && $response->headers->get('Content-Type') === null) {
            $response->headers->set('Content-Type', 'application/json');
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');
    }

    private function generateRequestId(): string
    {
        return sprintf(
            '%s-%s',
            date('YmdHis'),
            bin2hex(random_bytes(8))
        );
    }
}
