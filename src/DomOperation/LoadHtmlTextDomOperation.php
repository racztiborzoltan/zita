<?php
namespace Zita\DomOperation;

/**
 * Dom Operation for html text loading into \DOMDocument
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class LoadHtmlTextDomOperation extends AbstractLoadHtmlDomOperation
{

    /**
     * html text
     *
     * @var string
     */
    private $_html_text = null;

    /**
     * Set text of html
     *
     * @param string $html_text
     * @return self
     */
    public function setHtmlText(string $html_text): self
    {
        $this->_html_text = $html_text;
        return $this;
    }

    /**
     * Return text of html
     *
     * @throws \LogicException
     * @return string
     */
    public function getHtmlText(): string
    {
        if (empty($this->_html_text)) {
            throw new \LogicException('empty html text. Use the following method: ->setHtmlText()');
        }
        return $this->_html_text;
    }

    public function executeDomOperation(\DOMDocument $dom_document): \DOMDocument
    {
        return $this->_loadHtmlTextIntoDomDocument($this->getHtmlText(), $dom_document);
    }
}