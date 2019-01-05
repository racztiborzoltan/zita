<?php
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class TestMiddleware implements MiddlewareInterface
{

    private $_value = null;

    public function __construct(int $value)
    {
        $this->_value = $value;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $before_message = PHP_EOL . 'before handler - ' . __CLASS__ . ' ' . $this->_value;
        $GLOBALS['test_response_message_list'][] = $this->_value;

        $response = $handler->handle($request);
        $response_content = $before_message . (string)$response->getBody();

        $response = $response->withBody(\Nyholm\Psr7\Stream::create($response_content));
        $response->getBody()->write(PHP_EOL . 'after handler - ' . __CLASS__ . ' ' . $this->_value);
        $GLOBALS['test_response_message_list'][] = $this->_value;
        return $response;
    }
}
