<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class RequestLoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $this->logger->info("Incoming request: {$request->getMethod()} {$request->getUri()}", [
            'method' => $request->getMethod(),
            'uri'    => (string) $request->getUri(),
            'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user'   => $_SESSION['user_name']  ?? 'guest',
        ]);

        $response = $handler->handle($request);

        $this->logger->info("Response: {$response->getStatusCode()}", [
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
