<?php
namespace Zita;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Lazy Request Handler Middleware
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class LazyRequestHandlerMiddleware implements MiddlewareInterface
{

    use ApplicationAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $application = $this->getApplication();
        if (!$application->hasRequestHandler($request)) {
            throw new \LogicException('request handler not found!');
        }
        $request_handler = $application->getRequestHandler($request);
        return $request_handler->handle($request);
    }
}