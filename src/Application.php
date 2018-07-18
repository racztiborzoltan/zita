<?php
declare(strict_types=1);

namespace Zita;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class Application implements RequestHandlerInterface, MiddlewareInterface
{

    /**
     * @var MiddlewareList
     */
    private $_middleware_list = null;

    /**
     *
     * @var RequestHandlerInterface
     */
    private $_request_handler = null;

    protected static function _getLazyRequestHandlerMiddleware(Application $application): MiddlewareInterface
    {
        return (new class() implements MiddlewareInterface {

            /**
             *
             * @var Application
             */
            private $_application;

            public function setApplication(Application $application)
            {
                $this->_application = $application;
                return $this;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $this->_application->getRequestHandler()->handle($request);
            }
        })->setApplication($application);
    }

    protected function _prepareMiddlewares(): iterable
    {
        $middleware_list = clone $this->getMiddlewareList();
        $middlewares = [];
        foreach ($middleware_list as $middleware) {
            $middlewares[] = $middleware;
        }
        if ($this->issetRequestHandler()) {
            $middlewares[] = static::_getLazyRequestHandlerMiddleware($this);
        }
        return $middlewares;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middlewares = $this->_prepareMiddlewares();
        if (empty($middlewares)) {
            throw new \LogicException('There are no middleware or request handler. Use before ->getMiddlewareList()->add() and/or ->setRequestHandler() method.');
        }
        return (new \Atanvarno\Middleware\Dispatch\SimpleDispatcher(...$middlewares))->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return (new \Atanvarno\Middleware\Dispatch\SimpleDispatcher( ... $this->_prepareMiddlewares()))->process($request, $handler);
    }

    public function getMiddlewareList(): MiddlewareList
    {
        if (empty($this->_middleware_list)) {
            $this->_middleware_list = new MiddlewareList();
        }
        return $this->_middleware_list;
    }

    public function setMiddlewareList(MiddlewareList $middleware_list): self
    {
        $this->_middleware_list = $middleware_list;
        return $this;
    }

    public function setRequestHandler(RequestHandlerInterface $request_handler): Application
    {
        $this->_request_handler = $request_handler;
         return $this;
    }

    public function getRequestHandler(): RequestHandlerInterface
    {
        if (empty($this->_request_handler)) {
            throw new \LogicException('Empty request handler object. Use before ->setRequestHandler() method');
        }
        return $this->_request_handler;
    }

    public function issetRequestHandler(): bool
    {
        try {
            $this->getRequestHandler();
            return true;
        } catch (\LogicException $e) {
            return false;
        }
    }
}