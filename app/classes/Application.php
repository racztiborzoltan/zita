<?php
declare(strict_types=1);

namespace Zita\TestProject;

use Narrowspark\HttpEmitter\SapiEmitter;
use Zita\MiddlewareList;
use Zita\XsltPhpFunctionContainer;
use Nyholm\Psr7\Factory\Psr17Factory;
use DomOperationQueue\DomOperationQueue;
use Zita\DomOperation\LoadHtmlFileDomOperation;
use Zita\DomOperation\XsltDomOperation;

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

    /**
     * Name of DOM Operation list service in container object
     * @var string
     */
    const SERVICE_NAME_DOM_OPERATION_LIST = 'dom_operation_list';

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
     * Return DomOperationQueue object
     *
     * @return DomOperationQueue
     */
    public function getDomOperationList(): DomOperationQueue
    {
        return $this->getContainer()->get(static::SERVICE_NAME_DOM_OPERATION_LIST);
    }

    /**
     * initialization
     */
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

		    /**
		     * @var \Psr\SimpleCache\CacheInterface $cache
		     */
		    $cache = $application->getContainer()->get(static::SERVICE_NAME_SIMPLE_CACHE);

		    $cache_dir = realpath($application->getContainer()->get($application::SERVICE_NAME_BASE_DIR) . '/storage/cache');

		    $cache_id = 'caminar_sitebuild.cache';
		    $sitebuild_zip_path = $cache_dir . '/' . pathinfo($cache_id, PATHINFO_FILENAME) . '.zip';
		    if (!$cache->has($cache_id)) {
		        $sitebuild_zip_content = file_get_contents('https://templated.co/caminar/download');
		        $cache->set($cache_id, $sitebuild_zip_content);
		        file_put_contents($sitebuild_zip_path, $sitebuild_zip_content);
		    }
		    $unzip_destination = $cache_dir . '/' . pathinfo($cache_id, PATHINFO_FILENAME);
		    if (!is_dir($unzip_destination)) {
		        $zip = new \ZipArchive;
		        if ($zip->open($sitebuild_zip_path) === TRUE) {
		            $zip->extractTo($unzip_destination);
		            $zip->close();
		        } else {
		            throw new \LogicException('sitebuild unzip is failed: ' . $zip->getStatusString());
		        }
		    }

			$sitebuild = new SiteBuild();
			$base_dir = $application->getContainer()->get($application::SERVICE_NAME_BASE_DIR);
			$sitebuild->setSourceDirectory($unzip_destination);
			$sitebuild->setDestinationDirectory($base_dir.'/public');
			return $sitebuild;
		});

		// dom operation list service:
		$container->share($application::SERVICE_NAME_DOM_OPERATION_LIST, function(){
		    return new \DomOperationQueue\DomOperationQueue();
		});

		// cache service:
		$container->share($application::SERVICE_NAME_CACHE, function() use ($application){
		    $cache_dir = realpath($application->getContainer()->get($application::SERVICE_NAME_BASE_DIR) . '/storage/');
		    if (!$cache_dir) {
		        throw new \LogicException('cache dir is not exists!');
		    }
		    $filesystemAdapter = new \League\Flysystem\Adapter\Local($cache_dir . '/');
		    $filesystem        = new \League\Flysystem\Filesystem($filesystemAdapter);
		    return new \Cache\Adapter\Filesystem\FilesystemCachePool($filesystem, 'cache');
		});

		// simple cache service:
		$container->share($application::SERVICE_NAME_SIMPLE_CACHE, function() use ($application){
		    return $application->getContainer()->get($application::SERVICE_NAME_CACHE);
		});

		XsltPhpFunctionContainer::setContainer($container);

		$this->_initDomOperationList($application->getDomOperationList());
    }

    /**
     * Initialize the Dom Operation list
     *
     * @param DomOperationQueue $dom_operation_list
     */
    protected function _initDomOperationList(DomOperationQueue $dom_operation_list)
    {
		// https://templated.co/caminar/download

        /**
         * @var SiteBuild $sitebuild
         */
        $sitebuild = $this->getContainer()->get(static::SERVICE_NAME_SITEBUILD);

		$load_html_dom_operation = new LoadHtmlFileDomOperation();
		$load_html_dom_operation->setHtmlFilePath(realpath($sitebuild->getSourceDirectory() . '/index.html'));
		$dom_operation_list->add($load_html_dom_operation);

		$xslt_dom_operation = new XsltDomOperation();
		$xslt_dom_operation->loadXslFilePath(realpath('../app/template/index.xsl'));
		$dom_operation_list->add($xslt_dom_operation);

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
