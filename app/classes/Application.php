<?php
declare(strict_types=1);

namespace Zita\TestProject;

use Nyholm\Psr7\Factory\ServerRequestFactory;
use Narrowspark\HttpEmitter\SapiEmitter;
use Zita\MiddlewareList;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zita\ApplicationAwareInterface;
use Zita\ApplicationAwareTrait;
use Zita\XsltPhpFunctionContainer;
use Nyholm\Psr7\Factory\Psr17Factory;

class Application extends \Zita\Application
{

    /**
     * Name of base dir servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_BASE_DIR = 'base_dir';

    /**
     * Name of sitebuild servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_SITEBUILD = 'sitebuild';

    public function getBaseDir(): string
    {
        return $this->getContainer()->get(static::SERVICE_NAME_BASE_DIR);
    }

    protected function _init()
    {
        $application = $this;
		$container = new \League\Container\Container();
		$application->setContainer($container);

		// base dir service:
		$container->share($application::SERVICE_NAME_BASE_DIR, function(){
		    return realpath(dirname(dirname(__DIR__)));
		});

		// middleware list service:
		$container->share($application::SERVICE_NAME_MIDDLEWARE_LIST, function(){
			return new MiddlewareList();
		});

		// request service:
		$container->share($application::SERVICE_NAME_REQUEST, function(){
		    $method = $_SERVER['REQUEST_METHOD'];
		    $uri = $_SERVER['REQUEST_SCHEME']	 . '://' . $_SERVER['HTTP_HOST']	. $_SERVER['REQUEST_URI'];
		    return (new Psr17Factory())->createServerRequest($method, $uri, $_SERVER);
		});

		// request handler service:
		$container->share($application::SERVICE_NAME_REQUEST_HANDLER, function() use ($application){
		    $request_handler = new \Zita\TestProject\RequestHandler();
			$request_handler->setApplication($application);
			return $request_handler;
		});

		// response service:
		$container->share($application::SERVICE_NAME_RESPONSE, function(){
			return new \Nyholm\Psr7\Response();
		});

		// sitebuild service:
		$container->share($application::SERVICE_NAME_SITEBUILD, function() use ($application){
			$sitebuild = new SiteBuild();
			$base_dir = $application->getContainer()->get($application::SERVICE_NAME_BASE_DIR);
			$sitebuild->setSourceDirectory($base_dir.'/sitebuild');
			$sitebuild->setDestinationDirectory($base_dir.'/public');
			return $sitebuild;
		});

		XsltPhpFunctionContainer::setContainer($container);

		$prepare_sitebuild_middleware = new \Zita\TestProject\SiteBuildPrepareMiddleware();

		$base_dir = $application->getContainer()->get($application::SERVICE_NAME_BASE_DIR);
		$prepare_sitebuild_middleware->setHtmlFilePath(realpath($base_dir . '/sitebuild/index.html'));
		$prepare_sitebuild_middleware->setApplication($application);
		$application->getMiddlewareList()->add($prepare_sitebuild_middleware);
    }

    public function run()
    {
        $application = $this;

        $this->_init();

        $container = $application->getContainer();

        // request --> request handler --> response
        $request = $container->get($application::SERVICE_NAME_REQUEST);
        $request_handler = $application->getRequestHandler();
        $response = $application->process($request, $request_handler);

		//
		// @todo test with ->handler() method
		//
		// (new SapiEmitter())->emit($application->handle($request));
		(new SapiEmitter())->emit($response);
    }
}
