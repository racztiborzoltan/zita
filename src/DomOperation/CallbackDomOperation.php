<?php
namespace Zita\DomOperation;

/**
 * callback based Dom Operation
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
class CallbackDomOperation extends AbstractLoadHtmlDomOperation
{

    /**
     * callback
     *
     * @var callable
     */
    private $_callback = null;

    /**
     * Set callback
     *
     * @param callable $callback
     * @return self
     */
    public function setCallback(callable $callback): self
    {
        $this->_callback = $callback;
        return $this;
    }

    /**
     * Return callback
     *
     * @throws \LogicException
     * @return string
     */
    public function getCallback(): callable
    {
        if (empty($this->_callback)) {
            throw new \LogicException('empty callback. Use the following method: ->setCallback()');
        }
        return $this->_callback;
    }

    public function executeDomOperation(\DOMDocument $dom_document): \DOMDocument
    {
        $callback = $this->getCallback();
        return $callback($dom_document);
    }
}