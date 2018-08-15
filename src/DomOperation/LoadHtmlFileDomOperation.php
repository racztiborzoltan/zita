<?php
namespace Zita\DomOperation;

use DomOperationQueue\DomOperationInterface;

/**
 * Dom Operation for html file loading into \DOMDocument
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class LoadHtmlFileDomOperation implements DomOperationInterface
{

    /**
     * Path to html file
     *
     * @var string
     */
    private $_html_file_path = null;

    /**
     * Set path to html file
     *
     * @param string $html_file_path
     * @return self
     */
    public function setHtmlFilePath(string $html_file_path): self
    {
        $this->_html_file_path = $html_file_path;
        return $this;
    }

    /**
     * Return path to html file
     *
     * @throws \LogicException
     * @return string
     */
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

    public function executeDomOperation(\DOMDocument $dom_document): \DOMDocument
    {
        $html_file_path = $this->getHtmlFilePath();

        $html_file_content = file_get_contents($html_file_path);

        $prev_libxml_use_internal_errors = libxml_use_internal_errors(true);
        if (!empty($html_file_content)) {
            $dom_document->loadHTML($html_file_content);
        }
        libxml_use_internal_errors($prev_libxml_use_internal_errors);
        libxml_clear_errors();

        return $dom_document;
    }
}