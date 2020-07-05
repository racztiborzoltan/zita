<?php
namespace Zita;

use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareGroupInterface extends MiddlewareInterface
{

    public function clearMiddlewares();

    public function getMiddlewares(): iterable;

    public function setMiddleware(MiddlewareInterface $middleware, string $middleware_name = null);

    public function appendMiddleware(MiddlewareInterface $middleware, string $middleware_name = null);

    public function prependMiddleware(MiddlewareInterface $middleware, string $middleware_name = null);

    public function getMiddleware(string $middleware_name = null): ?MiddlewareInterface;

    public function removeMiddleware(string $middleware_name);
}
