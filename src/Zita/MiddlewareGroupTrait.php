<?php
namespace Zita;

use function Composer\Autoload\includeFile;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

trait MiddlewareGroupTrait
{

    private $_middlewares = [];

    public function clearMiddlewares()
    {
        $this->_middlewares = [];
        return $this;
    }

    public function getMiddlewares(): iterable
    {
        return $this->_middlewares;
    }

    public function setMiddleware(MiddlewareInterface $middleware, string $middleware_name = null)
    {
        if (is_null($middleware_name)) {
            $this->_middlewares[] = $middleware;
        } else {
            $this->_middlewares[$middleware_name] = $middleware;
        }
        return $this;
    }

    public function appendMiddleware(MiddlewareInterface $middleware, string $middleware_name = null)
    {
        $this->removeMiddleware($middleware_name);
        return $this->setMiddleware($middleware, $middleware_name);
    }

    public function prependMiddleware(MiddlewareInterface $middleware, string $middleware_name = null)
    {
        $this->removeMiddleware($middleware_name);
        $prev_middlewares = $this->getMiddlewares();
        $this->clearMiddlewares();
        $this->setMiddleware($middleware, $middleware_name);
        foreach ($prev_middlewares as $temp_name => $temp_middleware) {
            if (is_numeric($temp_name)) {
                $temp_name = null;
            }
            $this->setMiddleware($temp_middleware, $temp_name);
        }
        return $this;
    }

    public function getMiddleware(string $middleware_name = null): ?MiddlewareInterface
    {
        return $this->_middlewares[$middleware_name] ?? null;
    }

    public function removeMiddleware(string $middleware_name)
    {
        unset($this->_middlewares[$middleware_name]);
        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middlewares = $this->getMiddlewares();
        $middlewares[] = $handler;
        return (new \Relay\Relay($middlewares))->handle($request);
    }
}
