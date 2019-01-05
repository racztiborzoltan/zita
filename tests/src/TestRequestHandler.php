<?php
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestRequestHandler implements RequestHandlerInterface
{

    private $_value = null;

    public function __construct(int $value)
    {
        $this->_value = $value;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new \Nyholm\Psr7\Response())->withHeader('Content-Type', 'text/plain');
        $request_handler_message = $this->_value;
        $GLOBALS['test_response_message_list'][] = $request_handler_message;
        $response->getBody()->write(PHP_EOL . $request_handler_message);
        return $response;
    }
}
