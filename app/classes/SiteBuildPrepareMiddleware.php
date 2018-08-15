<?php
declare(strict_types=1);

namespace Zita\TestProject;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Stex\SimpleTemplateXslt;
use Zita\ApplicationAwareTrait;
use Zita\ApplicationAwareInterface;

class SiteBuildPrepareMiddleware implements MiddlewareInterface, ApplicationAwareInterface
{
    use ApplicationAwareTrait;

    /**
     * @var string
     */
    private $_html_file_path = null;

    public function setHtmlFilePath(string $html_file_path): self
    {
        $this->_html_file_path = $html_file_path;
        return $this;
    }

    public function getHtmlFilePath(): string
    {
        if (empty($this->_html_file_path)) {
            throw new \LogicException('empty html file path. Use the following method: ->setHtmlFilePath()');
        }
        if (!is_file($this->_html_file_path)) {
            throw new \LogicException('html file is not found');
        }
        return $this->_html_file_path;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        //
        // after request handler
        //

        $html_file_path = $this->getHtmlFilePath();

        $xslt_template = new SimpleTemplateXslt();

        $prev_internal = libxml_use_internal_errors(true);
        $xml_document = new \DOMDocument();
        $xml_document->loadHTML(file_get_contents($html_file_path));
        $xslt_template->setXmlDocument($xml_document);

        $xsl_document = new \DOMDocument();
        $xsl_document->loadXML(file_get_contents(realpath('../app/template/index.xsl')));
        $xslt_template->setXslDocument($xsl_document);

        libxml_use_internal_errors($prev_internal);

        $proc = $xslt_template->getXsltProcessor();
        $proc->registerPHPFunctions();

        $dom_document = $xslt_template->renderToDomDocument();

        $html_content = $dom_document->saveHTML($dom_document);

        $application = $this->getApplication();
        /**
         * @var \Psr\Http\Message\ResponseInterface $response
         */
        $response = $application->getContainer($application)->get($application::SERVICE_NAME_RESPONSE);
        $response->getBody()->write($html_content);

        return $response;
    }
}
