<?php

call_user_func(function(){
    /**
     * @var \Composer\Autoload\ClassLoader $autoloader
     */
    $autoloader = require_once '../vendor/autoload.php';
    $autoloader->add('', __DIR__.'/classes/');
});
