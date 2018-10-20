<?php
declare(strict_types=1);

namespace Zita\TestProject\Caminar;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zita\TestProject\ApplicationAwareTrait;
use Zita\DomOperationListAwareTrait;
use DomOperationQueue\DomOperationQueue;

class CaminarMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    use ApplicationAwareTrait;
    use DomOperationListAwareTrait;

    public function matchRequest(ServerRequestInterface $request): bool
    {
        return $this->getApplication()->getPathInfo()->getPath() === '/caminar';
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->matchRequest($request)) {
            $request = $request->withAttribute($this->getApplication()->getRequestHandlerAttributeName(), $this);
        }

        return $handler->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->getApplication()->getResponse();

        // default html content type if empty:
        $content_type_header = $response->getHeader('Content-Type');
        if (empty($content_type_header)) {
            $response = $response->withHeader('Content-Type', 'text/html;charset=utf-8');
        }

        $this->_initDomOperationList($this->getDomOperationList());

        return $this->_executeDomOperationList($response);
    }

    /**
     * Initialize the Dom Operation list
     *
     * @param DomOperationQueue $dom_operation_list
     */
    protected function _initDomOperationList(DomOperationQueue $dom_operation_list)
    {
		// https://templated.co/caminar/download

        $application = $this->getApplication();

        $dom_operation_template = new CaminarDomOperationTemplate();
        $dom_operation_template->setApplication($application);

        $dom_operation_template->setTemplateVariable('header_title', 'Zita Test Page');
        $dom_operation_template->setTemplateVariable('header_subtitle', 'by Caminar template');

        $dom_operation_list->add($dom_operation_template);
    }

    /**
     * Execute Dom Operation list on response object if possible
     *
     * @param ResponseInterface $response
     */
    protected function _executeDomOperationList(ResponseInterface $response): ResponseInterface
    {
        $content_type = $response->getHeader('Content-Type');
        $content_type = explode(';', reset($content_type));
        $content_type = array_filter($content_type);
        $content_type = reset($content_type);

        if (strpos($content_type, 'html') !== false) {
            /**
             * @var \Zita\TestProject\Application $application
             */
            $application = $this->getApplication();
            $dom_operation_list = $this->getDomOperationList();
            $dom_document = $this->_createDomDocumentFromResponse($response);
            /**
             * @var \DOMDocument $dom_document
             */
            $dom_document = $dom_operation_list->execute($dom_document);
            // replace response body:
            $response = $response->withBody(\Nyholm\Psr7\Stream::create(trim($dom_document->saveHTML($dom_document))));
        }

        return $response;
    }

    /**
     * Create \DOMDocument instance from response object
     *
     * @param ResponseInterface $response
     * @return \DOMDocument
     */
    protected function _createDomDocumentFromResponse(ResponseInterface $response): \DOMDocument
    {
        $response_body_raw_content = $response->getBody()->getContents();

        $dom_document = new \DOMDocument();
        $prev_libxml_use_internal_errors = libxml_use_internal_errors(true);
        if (!empty($response_body_raw_content)) {
            $dom_document->loadHTML($response_body_raw_content);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($prev_libxml_use_internal_errors);
        unset($prev_libxml_use_internal_errors);

        return $dom_document;
    }
}
