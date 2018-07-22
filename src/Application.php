<?php
declare(strict_types=1);

namespace Zita;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;

class Application implements RequestHandlerInterface, MiddlewareInterface
{

    /**
     * Name of middleware list servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_MIDDLEWARE_LIST = 'middleware_list';

    /**
     * Name of request servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_REQUEST = 'request';

    /**
     * Name of request handler servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_REQUEST_HANDLER = 'request_handler';

    /**
     * Name of response servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_RESPONSE = 'response';

    /**
     * PSR-11 container
     *
     * @var ContainerInterface
     */
    private $_container = null;

    /**
     * Returns instance of PSR-11 ContainerInterface
     *
     * @throws \LogicException
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if (empty($this->_container)) {
            throw new \LogicException('Container object is not found. Use the following method: ->setContainer()');
        }
        return $this->_container;
    }

    public function setContainer(ContainerInterface $container): self
    {
        $this->_container = $container;
        return $this;
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

    /**
     * Returns MiddlewareList object
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return MiddlewareList
     */
    public function getMiddlewareList(): MiddlewareList
    {
        return $this->getContainer()->get(static::SERVICE_NAME_MIDDLEWARE_LIST);
    }

    /**
     * Returns ServerRequestInterface object
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->getContainer()->get(static::SERVICE_NAME_REQUEST);
    }

    /**
     * Returns RequestHandlerInterface object
     *
     * @return RequestHandlerInterface
     */
    public function getRequestHandler(): RequestHandlerInterface
    {
        return $this->getContainer()->get(static::SERVICE_NAME_REQUEST_HANDLER);
    }

    /**
     * Returns RequestHandlerInterface object
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->getContainer()->get(static::SERVICE_NAME_RESPONSE);
    }

    public function issetRequestHandler(): bool
    {
        try {
            $this->getRequestHandler();
            return true;
        } catch (\Psr\Container\NotFoundExceptionInterface $e) {
            return false;
        }
    }

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
}