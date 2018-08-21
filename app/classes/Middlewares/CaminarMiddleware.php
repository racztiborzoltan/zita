<?php
declare(strict_types=1);

namespace Zita\TestProject\Middlewares;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Zita\TestProject\ApplicationAwareTrait;

class CaminarMiddleware implements MiddlewareInterface, RequestHandlerInterface
{
    use ApplicationAwareTrait;

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

        return $this->_executeDomOperationList($response);
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
            $dom_operation_list = $application->getDomOperationList();
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
