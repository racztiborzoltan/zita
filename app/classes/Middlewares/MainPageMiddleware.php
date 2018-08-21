<?php
declare(strict_types=1);

namespace Zita\TestProject\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zita\TestProject\ApplicationAwareTrait;

class MainPageMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    use ApplicationAwareTrait;

    public function matchRequest(ServerRequestInterface $request): bool
    {
        return $this->getApplication()->getPathInfo()->getPath() === '/';
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
        $response->getBody()->write('
            <a href="'.$this->getApplication()->getBaseUri().'/caminar">Caminar test page</a>
            <br />
        ');
        return $response;
    }
}
