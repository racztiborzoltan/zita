<?php

use Nyholm\Psr7\Factory\ServerRequestFactory;
use Narrowspark\HttpEmitter\SapiEmitter;
use Zita\MiddlewareList;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zita\ApplicationAwareInterface;
use Zita\ApplicationAwareTrait;
use Zita\TestProject\SiteBuild;
use Zita\XsltPhpFunctionContainer;


call_user_func(function(){
    /**
     * @var \Composer\Autoload\ClassLoader $autoloader
     */
    $autoloader = require_once '../vendor/autoload.php';
    $autoloader->addPsr4('Zita\\TestProject\\', __DIR__.'/../app/classes/');

    $prepare_sitebuild_middleware = new \Zita\TestProject\SiteBuildPrepareMiddleware();

    $application = new \Zita\TestProject\Application();

    $container = new League\Container\Container();

    // base dir service:
    $container->share($application::SERVICE_NAME_BASE_DIR, function(){
        return realpath(dirname(__DIR__));
    });

    // middleware list service:
    $container->share($application::SERVICE_NAME_MIDDLEWARE_LIST, function(){
        return new MiddlewareList();
    });

    // request service:
    $container->share($application::SERVICE_NAME_REQUEST, function(){
        return (new ServerRequestFactory())->createServerRequestFromGlobals();
    });

    // request handler service:
    $container->share($application::SERVICE_NAME_REQUEST_HANDLER, function() use ($application){
        $class = new class implements RequestHandlerInterface, ApplicationAwareInterface
        {
            use ApplicationAwareTrait;
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = $this->getApplication()->getResponse()->withHeader('Content-Type', 'text/plain');
                $response->getBody()->write(PHP_EOL . 'Hello Zita!');
                return $response;
            }
        };
        $class->setApplication($application);
        return $class;
    });

    // response service:
    $container->share($application::SERVICE_NAME_RESPONSE, function(){
        return new \Nyholm\Psr7\Response();
    });

    // sitebuild service:
    $container->share($application::SERVICE_NAME_SITEBUILD, function(){
        $sitebuild = new SiteBuild();
        $sitebuild->setSourceDirectory(__DIR__.'/../sitebuild');
        $sitebuild->setDestinationDirectory(__DIR__.'/../public');
        return $sitebuild;
    });

    XsltPhpFunctionContainer::setContainer($container);

    $application->setContainer($container);
    $request = $container->get($application::SERVICE_NAME_REQUEST);
    $request_handler = $application->getRequestHandler();

    $prepare_sitebuild_middleware->setHtmlFilePath(realpath(__DIR__ . '/../sitebuild/index.html'));
    $prepare_sitebuild_middleware->setApplication($application);

    $application->getMiddlewareList()->add($prepare_sitebuild_middleware);
    //
    // @todo test with ->handler() method
    //
    // (new SapiEmitter())->emit($application->handle($request));
    (new SapiEmitter())->emit($application->process($request, $request_handler));
});