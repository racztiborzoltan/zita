<?php

require_once __DIR__ . '/../vendor/autoload.php';

// class autoloader for test classes:
$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4('', __DIR__.'/src/', true);
$classLoader->register();
