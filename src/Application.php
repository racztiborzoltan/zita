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
     * Attribute name of request handler in request object
     *
     * @var string
     */
    private $_request_handler_attribute_name = 'request-handler';

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
        $middlewares[] = $this->_getLazyRequestHandlerMiddleware();
        return $middlewares;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return (new \Atanvarno\Middleware\Dispatch\SimpleDispatcher( ... $this->_prepareMiddlewares()))->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute($this->getRequestHandlerAttributeName(), $handler);
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
     * Set attribute name of request handler in request object
     *
     * @param string $attribute_name
     * @return self
     */
    public function setRequestHandlerAttributeName(string $attribute_name): self
    {
        $this->_request_handler_attribute_name = $attribute_name;
        return $this;
    }

    /**
     * Return attribute name of request handler in request object
     *
     * @return string
     */
    public function getRequestHandlerAttributeName()
    {
        return $this->_request_handler_attribute_name;
    }

    /**
     * Check if isset request handler object
     *
     * @param ServerRequestInterface $request optional request object
     * @return bool
     */
    public function hasRequestHandler(ServerRequestInterface $request = null): bool
    {
        if (is_null($request)) {
            $request = $this->getRequest();
        }
        $request_handler = $request->getAttribute($this->getRequestHandlerAttributeName());
        return !empty($request_handler) && $request_handler instanceof RequestHandlerInterface;
    }

    /**
     * Returns request handler object
     *
     * @param ServerRequestInterface $request optional request object
     * @return RequestHandlerInterface
     */
    public function getRequestHandler(ServerRequestInterface $request = null): RequestHandlerInterface
    {
        if (is_null($request)) {
            $request = $this->getRequest();
        }
        return $request->getAttribute($this->getRequestHandlerAttributeName());
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

    protected function _getLazyRequestHandlerMiddleware(): MiddlewareInterface
    {
        $middleware = new LazyRequestHandlerMiddleware();
        $middleware->setApplication($this);
        return $middleware;
    }
}
