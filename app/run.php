<?php



call_user_func(function(){
    /**
     * @var \Composer\Autoload\ClassLoader $autoloader
     */
    $autoloader = require_once '../vendor/autoload.php';
    $autoloader->addPsr4('Zita\\TestProject\\', __DIR__.'/../app/classes/');

    $application = new \Zita\TestProject\Application();
    $application->run();
});