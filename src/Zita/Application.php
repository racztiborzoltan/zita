<?php
namespace Zita;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Application implements RequestHandlerInterface, MiddlewareGroupInterface
{

    use MiddlewareGroupTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new \Relay\Relay($this->getMiddlewares()))->handle($request);
    }
}