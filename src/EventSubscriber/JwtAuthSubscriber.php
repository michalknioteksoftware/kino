<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Attribute\RequireJwt;
use App\Security\JwtInvalidException;
use App\Security\JwtChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class JwtAuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly JwtChecker $jwtChecker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 8]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        if ($route === null) {
            return;
        }

        $controller = $request->attributes->get('_controller');
        if (!is_string($controller)) {
            return;
        }

        if (!$this->routeRequiresJwt($request->attributes->get('_controller'))) {
            return;
        }

        $auth = $request->headers->get('Authorization', '');
        try {
            $this->jwtChecker->validateBearer($auth);
        } catch (JwtInvalidException $e) {
            $event->setResponse(new JsonResponse([
                'error' => 'Unauthorized',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/json']));
        }
    }

    private function routeRequiresJwt(?string $controller): bool
    {
        if ($controller === null) {
            return false;
        }
        if (!str_contains($controller, '::')) {
            return false;
        }
        [$class, $method] = explode('::', $controller, 2);
        if (!class_exists($class)) {
            return false;
        }
        $ref = new \ReflectionClass($class);
        if ($ref->getMethod($method)->getAttributes(RequireJwt::class) !== []) {
            return true;
        }
        if ($ref->getAttributes(RequireJwt::class) !== []) {
            return true;
        }
        return false;
    }
}
