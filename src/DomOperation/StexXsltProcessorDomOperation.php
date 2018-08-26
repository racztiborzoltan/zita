<?php
namespace Zita\DomOperation;

use DomOperationQueue\DomOperationInterface;
use Stex\StexXsltProcessor;

/**
 * Dom Operation for StexXsltProcesor
 *
 * XML document will be the first arugment of ->executeDomOperation() method
 * XSL document can be set with ->loadXslFilePath() or ->setXslDocument() methods.
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class StexXsltProcessorDomOperation implements DomOperationInterface
{

    /**
     * stex instance
     *
     * @var StexXsltProcessor
     */
    private $_stex = null;

    /**
     * XSL(T) document object
     *
     * @var \DOMDocument
     */
    private $_xsl_document = null;

    /**
     * Set stex instance
     *
     * @param StexXsltProcessor $stex
     * @return self
     */
    public function setStex(StexXsltProcessor $stex): self
    {
        $this->_stex = $stex;
        return $this;
    }

    /**
     * Returns stex instance
     *
     * @param StexXsltProcessor $stex
     * @return self
     */
    public function getStex(): StexXsltProcessor
    {
        if (empty($this->_stex)) {
            throw new \LogicException('stex object is not set. Use before the following method: ->setStex()');
        }
        return $this->_stex;
    }

    /**
     * Set XSL(T) document
     *
     * @param \DOMDocument $xsl_document
     * @return self
     */
    public function setXslDocument(\DOMDocument $xsl_document): self
    {
        $this->_xsl_document = $xsl_document;
        return $this;
    }

    /**
     * Returns XSL(T) document
     *
     * @throws \LogicException
     * @return \DOMDocument
     */
    public function getXslDocument(): \DOMDocument
    {
        if (empty($this->_xsl_document)) {
            throw new \LogicException('empty xsl document. Use the following methods: ->setXslDocument() or ->loadXslFilePath()');
        }
        // If present file path:
        if (is_string($this->_xsl_document)) {
            if (!is_file($this->_xsl_document)) {
                throw new \LogicException('xsl file path is not found');
            }
            $prev_libxml_use_internal_errors = libxml_use_internal_errors(true);
            $xsl_document = new \DOMDocument();
            $xsl_document->loadXML(file_get_contents($this->_xsl_document));
            $this->_xsl_document = $xsl_document;
            libxml_use_internal_errors($prev_libxml_use_internal_errors);
            libxml_clear_errors();
        }
        return $this->_xsl_document;
    }

    /**
     * Set file path to xsl with lazy load
     *
     * @param string $xsl_file_path
     * @return self
     */
    public function loadXslFilePath(string $xsl_file_path): self
    {
        $this->_xsl_document = $xsl_file_path;
        return $this;
    }

    public function executeDomOperation(\DOMDocument $dom_document): \DOMDocument
    {
        $stex = $this->getStex();

        $xml_document = $dom_document;

        /**
         * @var \DOMDocument $xsl_document
         */
        $xsl_document = $this->getXslDocument();
        $stex->importStylesheet($xsl_document);

        $stex->registerPHPFunctions();

        return $stex->transformToDoc($xml_document);
    }
}