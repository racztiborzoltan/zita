<?php

namespace Zita\TestProject;

use Zita\TestProject\Caminar\CaminarMiddleware;
use Zita\TestProject\Middlewares\MainPageMiddleware;
use Zita\TestProject\Middlewares\PageNotFoundMiddleware;
use Narrowspark\HttpEmitter\SapiEmitter;

call_user_func(function(){

    /**
     * @var \Composer\Autoload\ClassLoader $autoloader
     */
    $autoloader = require_once '../vendor/autoload.php';
    $autoloader->addPsr4('Zita\\TestProject\\', __DIR__.'/../app/classes/');

    $application = new \Zita\TestProject\Application();

    $application->init();

    $application->getMiddlewareList()->add((new CaminarMiddleware())->setApplication($application));
    $application->getMiddlewareList()->add((new MainPageMiddleware())->setApplication($application));
    $application->getMiddlewareList()->add((new PageNotFoundMiddleware())->setApplication($application));

    // request --> request handler --> response
    $request = $application->getRequest();
    $response = $application->handle($request);

    (new SapiEmitter())->emit($response);

});