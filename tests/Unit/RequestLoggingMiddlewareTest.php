<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Logger;
use App\Core\Middleware\RequestLoggingMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestLoggingMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REMOTE_ADDR']);
        unset($_SESSION['user_name']);
    }

    public function testProcessLogsRequestAndReturnsResponse(): void
    {
        $logger     = new Logger();
        $middleware = new RequestLoggingMiddleware($logger);

        $request  = new ServerRequest('GET', '/test');
        $response = new Response(200);

        $handler = new class ($response) implements RequestHandlerInterface {
            public function __construct(private readonly ResponseInterface $response)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        $result = $middleware->process($request, $handler);

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testProcessLogsWithSessionUser(): void
    {
        $_SESSION['user_name'] = 'Elvina';

        $logger     = new Logger();
        $middleware = new RequestLoggingMiddleware($logger);

        $request  = new ServerRequest('POST', '/recipes/create');
        $response = new Response(201);

        $handler = new class ($response) implements RequestHandlerInterface {
            public function __construct(private readonly ResponseInterface $response)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        $result = $middleware->process($request, $handler);

        $this->assertSame(201, $result->getStatusCode());
    }
}
