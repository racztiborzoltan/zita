<?php

use Zita\Application;
use Nyholm\Psr7\Factory\ServerRequestFactory;
use Narrowspark\HttpEmitter\SapiEmitter;

call_user_func(function(){
    require_once 'autoload.php';

    header('Content-Type: text/plain');

    $middlewares = [];
    $middlewares[1] = new TestMiddleware(1);
    $middlewares[2] = new TestMiddleware(2);
    $middlewares[3] = new TestMiddleware(3);

    $app = new Application();
    foreach ($middlewares as $middleware) {
        $app->getMiddlewareList()->add($middleware);
        unset($middleware);
    }
    (new SapiEmitter())->emit($app->process((new ServerRequestFactory())->createServerRequestFromGlobals(), new TestRequestHandler()));
});
