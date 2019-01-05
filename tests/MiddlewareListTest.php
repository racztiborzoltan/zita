<?php
declare(strict_types=1);

namespace Zita\Tests;

use PHPUnit\Framework\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;

final class MiddlewareListTest extends TestCase
{

    /**
     * @var \Zita\Application
     */
    private $_application = null;

    public function setUp()
    {

        $middlewares = [];
        $middlewares[1] = new \TestMiddleware(1);
        $middlewares[2] = new \TestMiddleware(2);
        $middlewares[3] = new \TestMiddleware(3);

        $application = new \Zita\Application();
        $container = $this->_factoryContainer($application);
        $application->setContainer($container);

        foreach ($middlewares as $middleware) {
            $application->getMiddlewareList()->add($middleware);
        }
        unset($middleware, $middlewares);

        $this->_application = $application;
    }

    public function test_handle()
    {
        $GLOBALS['test_response_message_list'] = [];

        $application = $this->_application;
        $request = $this->_application->getRequest();

        // manual adding request handler
        $request = $request->withAttribute($application->getRequestHandlerAttributeName(), new \TestRequestHandler(0));

        // request --> request handler --> response
        $application->handle($request);

        $this->assertEquals([ 1,2,3,0,3,2,1 ], $GLOBALS['test_response_message_list']);
   }

    public function test_process()
    {
        $GLOBALS['test_response_message_list'] = [];

        $application = $this->_application;
        $request = $this->_application->getRequest();

        // request --> request handler --> response
        $application->process($request, new \TestRequestHandler(0));

        $this->assertEquals([ 1,2,3,0,3,2,1 ], $GLOBALS['test_response_message_list']);
    }

   protected function _factoryContainer(\Zita\Application $application)
   {
        $container = new \League\Container\Container();

        // middleware list service:
        $container->share($application::SERVICE_NAME_MIDDLEWARE_LIST, function(){
           return new \Zita\MiddlewareList();
        });

        // request service:
        $container->share($application::SERVICE_NAME_REQUEST, function(){
            $method = $_SERVER['REQUEST_METHOD'] ?? '';
            $uri = ($_SERVER['REQUEST_SCHEME'] ?? '')	 . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
            return (new Psr17Factory())->createServerRequest($method, $uri, $_SERVER);
        });

        // response service:
        $container->share($application::SERVICE_NAME_RESPONSE, function(){
            return new \Nyholm\Psr7\Response();
        });

       return $container;
   }
}
