<?php
declare(strict_types=1);

namespace Zita\TestProject\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zita\TestProject\ApplicationAwareTrait;

/**
 * Simple "Page not found" middleware
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class PageNotFoundMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    use ApplicationAwareTrait;

    public function matchRequest(ServerRequestInterface $request): bool
    {
        return !$this->getApplication()->hasRequestHandler($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->matchRequest($request)) {
            $request = $request->withAttribute($this->getApplication()->getRequestHandlerAttributeName(), $this);
        }

        return $handler->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->getApplication()->getResponse();
        $response->getBody()->write('404 - Page not found! (Zita)');
        $response = $response->withHeader('Content-Type', 'text/plain');
        $response = $response->withStatus('404');
        return $response;
    }
}
