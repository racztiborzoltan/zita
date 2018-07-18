<?php
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestRequestHandler implements RequestHandlerInterface
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new \Nyholm\Psr7\Response())->withHeader('Content-Type', 'text/plain');
        $response->getBody()->write(PHP_EOL . '    handler response');
        return $response;
    }
}
