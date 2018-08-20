<?php
namespace Zita\DomOperation;

use DomOperationQueue\DomOperationInterface;

/**
 * Abstract class for html loading dom operations
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
abstract class AbstractLoadHtmlDomOperation implements DomOperationInterface
{

    /**
     * Options for html loading
     *
     * Possible contants: LIBXML_*
     *
     * @var integer
     */
    private $_load_options = null;

    /**
     * Set options for loading
     *
     * @param int $load_options
     * @return self
     */
    public function setLoadOptions(?int $load_options): self
    {
        $this->_load_options = $load_options;
        return $this;
    }

    /**
     * Return options for loading
     *
     * @param integer $load_options
     * @return integer
     */
    public function getLoadOptions(): int
    {
        return (int)$this->_load_options;
    }

    /**
     * Load html text into \DOMDocument object
     *
     * @param string $html_text
     * @param \DOMDocument $dom_document
     * @return \DOMDocument dom document object with loaded html text
     */
    protected function _loadHtmlTextIntoDomDocument(string $html_text, \DOMDocument $dom_document): \DOMDocument
    {
        $prev_libxml_use_internal_errors = libxml_use_internal_errors(true);
        if (!empty($html_text)) {
            $dom_document->loadHTML($html_text, $this->getLoadOptions());
        }
        libxml_use_internal_errors($prev_libxml_use_internal_errors);
        libxml_clear_errors();
        return $dom_document;
    }
}