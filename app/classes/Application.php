<?php
declare(strict_types=1);

namespace Zita\TestProject;

use Zita\MiddlewareList;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\UriInterface;

class Application extends \Zita\Application
{

    /**
     * Name of base dir servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_BASE_DIR = 'base_dir';

    /**
     * Name of public dir servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_PUBLIC_DIR = 'public_dir';

    /**
     * Name of base uri servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_BASE_URI = 'base_uri';

    /**
     * Name of path info servce in container object
     *
     * @var string
     */
    const SERVICE_NAME_PATH_INFO = 'path_info';

    /**
     * Name of cache service in container object
     * @var string
     */
    const SERVICE_NAME_CACHE = 'cache';

    /**
     * Name of simple cache service in container object
     * @var string
     */
    const SERVICE_NAME_SIMPLE_CACHE = 'simple_cache';

    /**
     * Return base dir of application
     *
     * @return string
     */
    public function getBaseDir(): string
    {
        return $this->getContainer()->get(static::SERVICE_NAME_BASE_DIR);
    }

    /**
     * Return public dir of application
     *
     * @return string
     */
    public function getPublicDir(): string
    {
        return $this->getContainer()->get(static::SERVICE_NAME_PUBLIC_DIR);
    }

    /**
     * Return base uri of application
     *
     * @return string
     */
    public function getBaseUri(): UriInterface
    {
        return $this->getContainer()->get(static::SERVICE_NAME_BASE_URI);
    }

    /**
     * Return path info of application
     *
     * @return UriInterface
     */
    public function getPathInfo(): UriInterface
    {
        return $this->getContainer()->get(static::SERVICE_NAME_PATH_INFO);
    }

    /**
     * Return cache object
     *
     * @return \Psr\Cache\CacheItemPoolInterface
     */
    public function getCache(): \Psr\Cache\CacheItemPoolInterface
    {
        return $this->getContainer()->get(static::SERVICE_NAME_CACHE);
    }

    /**
     * Return simple cache object
     *
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function getSimpleCache(): \Psr\SimpleCache\CacheInterface
    {
        return $this->getContainer()->get(static::SERVICE_NAME_SIMPLE_CACHE);
    }

    /**
     * initialization
     *
     * @throws \LogicException
     * @return self
     */
    public function init(): self
    {
        $application = $this;
		$container = new \League\Container\Container();
		$application->setContainer($container);

		// base dir service:
		$container->share($application::SERVICE_NAME_BASE_DIR, function(){
		    return realpath(dirname(dirname(__DIR__)));
		});

		// public dir service:
		$container->share($application::SERVICE_NAME_PUBLIC_DIR, function() use ($container, $application){
		    return $container->get($application::SERVICE_NAME_BASE_DIR) . '/public';
		});

		// base uri service:
		$container->share($application::SERVICE_NAME_BASE_URI, function(){

		    $path = $_SERVER['SCRIPT_NAME'];
		    if (substr_count($path, '/') < 2) {
		        $path = '';
		    } else {
		        $path = str_replace('\\', '/', dirname($path));
		    }
		    return new \Nyholm\Psr7\Uri($_SERVER['REQUEST_SCHEME']	 . '://' . $_SERVER['HTTP_HOST']	. $path);
		});

		// path info service:
		$container->share($application::SERVICE_NAME_PATH_INFO, function(){
		    $request = $this->getRequest();
		    $request_uri = $request->getUri();
		    $base_uri = $this->getBaseUri();

		    $path_info = preg_replace('#^'.preg_quote($base_uri->getPath()).'#', '', $request_uri->getPath());

		    return $base_uri->withPath($path_info);
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

		// response service:
		$container->share($application::SERVICE_NAME_RESPONSE, function(){
			return new \Nyholm\Psr7\Response();
		});

// 		// dom operation list service:
// 		$container->share($application::SERVICE_NAME_DOM_OPERATION_LIST, function(){
// 		    return new \DomOperationQueue\DomOperationQueue();
// 		});

		// cache service:
		$container->share($application::SERVICE_NAME_CACHE, function() use ($application){
		    $cache_dir = realpath($application->getBaseDir() . '/storage/');
		    if (!$cache_dir) {
		        throw new \LogicException('cache dir is not exists!');
		    }
		    $filesystemAdapter = new \League\Flysystem\Adapter\Local($cache_dir . '/');
		    $filesystem        = new \League\Flysystem\Filesystem($filesystemAdapter);
		    return new \Cache\Adapter\Filesystem\FilesystemCachePool($filesystem, 'cache');
		});

		// simple cache service:
		$container->share($application::SERVICE_NAME_SIMPLE_CACHE, function() use ($application){
		    return $application->getCache();
		});

		return $this;
    }
}
