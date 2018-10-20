<?php
namespace Zita\DomOperation;

use Psr\Http\Message\ResponseInterface;

/**
 * Dom Operation for html text loading into \DOMDocument from PSR-7 Response object
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class LoadHtmlFromResponseDomOperation extends AbstractLoadHtmlDomOperation
{

    /**
     * Response object
     *
     * @var ResponseInterface
     */
    private $_response = null;

    /**
     * Set response object
     *
     * @param string $response
     * @return self
     */
    public function setResponse(ResponseInterface $response): self
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Return response object
     *
     * @throws \LogicException
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        if (empty($this->_response)) {
            throw new \LogicException('empty response object. Use the following method: ->setResponse()');
        }
        return $this->_response;
    }

    public function executeDomOperation(\DOMDocument $dom_document): \DOMDocument
    {
        return $this->_loadHtmlTextIntoDomDocument($this->getResponse()->getBody()->getContents(), $dom_document);
    }
}