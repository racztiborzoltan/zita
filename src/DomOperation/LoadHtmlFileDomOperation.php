<?php
namespace Zita\DomOperation;

/**
 * Dom Operation for html file loading into \DOMDocument
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class LoadHtmlFileDomOperation extends AbstractLoadHtmlDomOperation
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
        return $this->_loadHtmlTextIntoDomDocument(file_get_contents($this->getHtmlFilePath()), $dom_document);
    }
}