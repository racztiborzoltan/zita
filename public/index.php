<?php

use Zita\Application;
use Nyholm\Psr7\Factory\ServerRequestFactory;
use Narrowspark\HttpEmitter\SapiEmitter;

call_user_func(function(){
    /**
     * @var \Composer\Autoload\ClassLoader $autoloader
     */
    $autoloader = require_once '../vendor/autoload.php';
    $autoloader->addPsr4('Zita\\', __DIR__.'/../app/classes/');

    $app = new Application();
//     $app->addMiddleware(new \Zita\LoginMiddleware());
    $app->getMiddlewareList()->add(new \Zita\AdminMiddleware());
    $app->setRequestHandler(new TestRequestHandler());
    (new SapiEmitter())->emit($app->handle((new ServerRequestFactory())->createServerRequestFromGlobals()));
});